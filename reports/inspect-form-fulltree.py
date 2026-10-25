import json,sys,re,html
sys.stdout.reconfigure(encoding='utf-8')
for pid,file in [(7,'elementor-7-before.json'),(2015,'elementor-2015-reference.json')]:
 d=json.load(open('reports/backups/home-form-style-20261016/'+file,encoding='utf-8-sig'))
 print('\n=== TREE',pid,'===')
 def clean(v): return ' '.join(html.unescape(re.sub('<[^>]+>',' ',str(v))).split())[:200]
 def walk(xs,depth=0):
  for x in xs:
   if not isinstance(x,dict): continue
   s=x.get('settings',{}) if isinstance(x.get('settings'),dict) else {}
   v=s.get('title') or s.get('editor') or s.get('text') or s.get('form_name') or s.get('html') or s.get('shortcode') or ''
   print('  '*depth+f"{x.get('id')} {x.get('elType')} {x.get('widgetType','')} | {clean(v)}")
   walk(x.get('elements',[]),depth+1)
 walk(d)
