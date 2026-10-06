<?php
/**
 * Generate WebP siblings for large JPEG and PNG files in an uploads tree.
 */

$root = $argv[1] ?? '';
if ( ! is_dir( $root ) ) {
	fwrite( STDERR, "Usage: php generate-webp.php /path/to/uploads\n" );
	exit( 1 );
}

$minimum_bytes = 50 * 1024;
$converted     = 0;
$skipped       = 0;
$failed        = 0;
$source_bytes  = 0;
$webp_bytes    = 0;
$iterator      = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);

foreach ( $iterator as $file ) {
	if ( ! $file->isFile() || ! preg_match( '/\.(?:jpe?g|png)$/i', $file->getFilename() ) ) {
		continue;
	}

	$source = $file->getPathname();
	$target = $source . '.webp';
	if ( $file->getSize() < $minimum_bytes || ( is_file( $target ) && filemtime( $target ) >= $file->getMTime() ) ) {
		++$skipped;
		continue;
	}

	try {
		$image = new Imagick( $source );
		$image->setIteratorIndex( 0 );
		$image->setImageFormat( 'webp' );
		$image->setImageCompressionQuality( 82 );
		$image->setOption( 'webp:method', '6' );
		$image->stripImage();
		if ( ! $image->writeImage( $target ) ) {
			throw new RuntimeException( 'writeImage returned false' );
		}
		$source_bytes += $file->getSize();
		$webp_bytes   += filesize( $target );
		++$converted;
		$image->clear();
		$image->destroy();
	} catch ( Throwable $error ) {
		++$failed;
		fwrite( STDERR, "Failed: {$source}: {$error->getMessage()}\n" );
	}
}

printf(
	"CONVERTED=%d\nSKIPPED=%d\nFAILED=%d\nSOURCE_MB=%.2f\nWEBP_MB=%.2f\nSAVED_PERCENT=%.1f\n",
	$converted,
	$skipped,
	$failed,
	$source_bytes / 1048576,
	$webp_bytes / 1048576,
	$source_bytes ? ( 1 - ( $webp_bytes / $source_bytes ) ) * 100 : 0
);
