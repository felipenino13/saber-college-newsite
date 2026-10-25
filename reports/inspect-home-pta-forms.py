import json,sys,re
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')
for pid in (7,872):
 p=Path(f'reports/backups/home-form-style-20261016/elementor-{pid}-before.json')
 d=json.load(open(p,encoding='utf-8-sig'))
 print(f'\n=== PAGE {pid} ===')
 def walk(xs,path=[]):
  for x in xs:
   if not isinstance(x,dict): continue
   s=x.get('settings',{}) if isinstance(x.get('settings'),dict) else {}
   name=s.get('_title') or s.get('form_name') or s.get('title') or ''
   if x.get('widgetType') in ('form','shortcode','html') or any(k in str(s).lower() for k in ['form-field','request information']):
    print('PATH',' > '.join([a for a in path+[x.get('id','?')] if a]))
    print('NODE',x.get('id'),x.get('elType'),x.get('widgetType'), 'NAME',str(name)[:100])
    print(json.dumps(s,ensure_ascii=False,indent=2)[:12000])
   walk(x.get('elements',[]),path+[x.get('id','?')])
 walk(d)
