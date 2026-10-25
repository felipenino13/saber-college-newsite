import json,sys
sys.stdout.reconfigure(encoding='utf-8')
d=json.load(open(sys.argv[1],encoding='utf-8'))
def walk(xs):
 for x in xs:
  if not isinstance(x,dict): continue
  s=x.get('settings',{}) if isinstance(x.get('settings',{}),dict) else {}
  if x.get('widgetType') in ('image','form'):
   print('\nID',x.get('id'),'TYPE',x.get('widgetType'))
   print(json.dumps(s,ensure_ascii=False,indent=2))
  walk(x.get('elements',[]))
walk(d)
