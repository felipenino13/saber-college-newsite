$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$raw = Join-Path $root 'raw'
New-Item -ItemType Directory -Force -Path $raw | Out-Null

$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
$pages = @(
    @{ slug = 'home'; url = 'http://localhost:8082/' },
    @{ slug = 'programs'; url = 'http://localhost:8082/saber_college_programs/' },
    @{ slug = 'professional-nursing'; url = 'http://localhost:8082/saber_college_programs/professional-nursing-program/' },
    @{ slug = 'physical-therapist-assistant'; url = 'http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/' },
    @{ slug = 'admissions-tuition'; url = 'http://localhost:8082/admissions-and-tuition/' },
    @{ slug = 'student-services'; url = 'http://localhost:8082/student-services/' },
    @{ slug = 'general'; url = 'http://localhost:8082/general/' },
    @{ slug = 'lp-pnp'; url = 'http://localhost:8082/lp/pnp/' },
    @{ slug = 'lp-pta'; url = 'http://localhost:8082/lp/pta/' }
)

$manifest = @()

function Invoke-LighthouseAudit {
    param(
        [string]$Slug,
        [string]$Url,
        [string]$FormFactor,
        [int]$Run,
        [switch]$Html
    )

    $name = "$Slug-$FormFactor-run$Run"
    $output = Join-Path $raw $name
    Write-Host "START $name $Url"
    $arguments = @(
        '--yes', 'lighthouse@13.5.0', $Url,
        '--only-categories=performance',
        '--chrome-path', $chrome,
        '--chrome-flags=--headless --no-sandbox --disable-gpu',
        '--quiet',
        '--form-factor', $FormFactor
    )
    if ($FormFactor -eq 'desktop') {
        $arguments += '--preset=desktop'
    }
    if ($Html) {
        $arguments += @('--output=json', '--output=html', '--output-path', $output)
        & npx.cmd @arguments
        $jsonPath = "$output.report.json"
        $htmlPath = "$output.report.html"
    } else {
        $jsonPath = "$output.json"
        $htmlPath = $null
        $arguments += @('--output=json', '--output-path', $jsonPath)
        & npx.cmd @arguments
    }
    if ($LASTEXITCODE -ne 0 -or !(Test-Path $jsonPath)) {
        throw "Lighthouse failed for $name"
    }
    $lhr = Get-Content $jsonPath -Raw | ConvertFrom-Json
    $score = [math]::Round(100 * $lhr.categories.performance.score)
    Write-Host "DONE  $name score=$score"
    return [pscustomobject]@{
        slug = $Slug
        url = $Url
        form_factor = $FormFactor
        run = $Run
        score = $score
        json = (Resolve-Path $jsonPath).Path
        html = if ($htmlPath) { (Resolve-Path $htmlPath).Path } else { $null }
        fetch_time = $lhr.fetchTime
        lighthouse_version = $lhr.lighthouseVersion
    }
}

foreach ($page in $pages) {
    $manifest += Invoke-LighthouseAudit -Slug $page.slug -Url $page.url -FormFactor 'mobile' -Run 1 -Html:($page.slug -eq 'home')
}

$manifest += Invoke-LighthouseAudit -Slug 'home' -Url 'http://localhost:8082/' -FormFactor 'mobile' -Run 2
$manifest += Invoke-LighthouseAudit -Slug 'home' -Url 'http://localhost:8082/' -FormFactor 'mobile' -Run 3
$manifest += Invoke-LighthouseAudit -Slug 'home' -Url 'http://localhost:8082/' -FormFactor 'desktop' -Run 1 -Html

$manifest | ConvertTo-Json -Depth 5 | Set-Content -Encoding utf8 (Join-Path $root 'run-manifest.json')
$manifest | Format-Table slug, form_factor, run, score
