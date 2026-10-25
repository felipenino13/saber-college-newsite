import json,re,html,hashlib
from pathlib import Path
base=Path('reports/backups/home-form-style-20261016')
before=json.load(open(base/'elementor-7-before.json',encoding='utf-8-sig'))
after=json.load(open(base/'elementor-7-after.json',encoding='utf-8-sig'))

def walk(xs):
 for x in xs:
  if isinstance(x,dict):
   yield x
   yield from walk(x.get('elements',[]))

def find(data,id): return next(x for x in walk(data) if x.get('id')==id)
def counts(data):
 out={}
 for x in walk(data):
  k=x.get('widgetType') or x.get('elType','unknown'); out[k]=out.get(k,0)+1
 return out
def vis(data):
 out=[]
 for x in walk(data):
  if x.get('widgetType')=='html': continue
  s=x.get('settings',{}) if isinstance(x.get('settings'),dict) else {}
  for k in ('title','editor','text','button_text'):
   if s.get(k):
    v=' '.join(html.unescape(re.sub('<[^>]+>',' ',str(s[k]))).split())
    if v: out.append(v)
 return out
ref_before=(base/'elementor-872-before.json').read_bytes()
ref_after=(base/'elementor-872-after.json').read_bytes()
tpl_before=(base/'elementor-2015-reference.json').read_bytes()
tpl_after=(base/'elementor-2015-after.json').read_bytes()
summary={
 'nodeCountsBefore':counts(before),'nodeCountsAfter':counts(after),'nodeCountsUnchanged':counts(before)==counts(after),
 'formSettingsUnchanged':find(before,'b7159a9').get('settings')==find(after,'b7159a9').get('settings'),
 'visibleTextUnchanged':vis(before)==vis(after),'visibleTextCount':len(vis(before)),
 'referencePage872Unchanged':hashlib.sha256(ref_before).hexdigest()==hashlib.sha256(ref_after).hexdigest(),
 'referenceTemplate2015Unchanged':hashlib.sha256(tpl_before).hexdigest()==hashlib.sha256(tpl_after).hexdigest(),
 'oldSelectorsRemaining':(base/'home-after.html').read_text(encoding='utf-8-sig').count('.elementor-2015 .elementor-element.elementor-element-f698ff6'),
 'fixedSelectorsRendered':(base/'home-after.html').read_text(encoding='utf-8-sig').count('.elementor-7 .elementor-element.elementor-element-b7159a9')
}
Path('reports/home-form-style-structural-20261016.json').write_text(json.dumps(summary,indent=2),encoding='utf-8')
print(json.dumps(summary,indent=2))
