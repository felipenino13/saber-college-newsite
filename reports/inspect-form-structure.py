import json,sys,re,html
sys.stdout.reconfigure(encoding='utf-8')
for pid in (7,2015):
 d=json.load(open(f'reports/backups/home-form-style-20261016/elementor-{pid}-'+('before.json' if pid==7 else 'reference.json'),encoding='utf-8-sig'))
 print('\n===',pid,'===')
 def clean(v): return ' '.join(html.unescape(re.sub('<[^>]+>',' ',str(v))).split())[:180]
 def walk(xs,depth=0,active=False):
  for x in xs:
   if not isinstance(x,dict): continue
   s=x.get('settings',{}) if isinstance(x.get('settings'),dict) else {}
   now=active or x.get('id') in ('e8c144c','b7159a9','f698ff6')
   if now:
    val=s.get('title') or s.get('editor') or s.get('text') or s.get('html') or s.get('shortcode') or ''
    print('  '*depth,x.get('id'),x.get('elType'),x.get('widgetType',''),'|',clean(val))
   walk(x.get('elements',[]),depth+1,now)
 walk(d)
