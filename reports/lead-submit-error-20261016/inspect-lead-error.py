import json,re,sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')
p=Path('reports/lead-submit-error-20261016')
d=json.load(open(p/'elementor-2015-before.json',encoding='utf-8-sig'))
def walk(xs):
 for x in xs:
  if isinstance(x,dict):
   yield x
   yield from walk(x.get('elements',[]))
for x in walk(d):
 if x.get('widgetType')=='form':
  print('FORM',x.get('id'))
  for f in x.get('settings',{}).get('form_fields',[]):
   print(f.get('_id'),f.get('field_type','text'),f.get('custom_id'),f.get('field_label'),repr(f.get('field_value')))
  print('submit_actions',x.get('settings',{}).get('submit_actions'))
 if x.get('widgetType')=='html' and 'querySelector' in str(x.get('settings',{}).get('html','')):
  html=x['settings']['html']
  print('\nSCRIPT_WIDGET',x.get('id'))
  for i,line in enumerate(html.splitlines(),1):
   if 'querySelector' in line or '.value' in line or 'addEventListener' in line:
    print(f'{i}: {line}')
html=(p/'pta-rendered-before.html').read_text(encoding='utf-8-sig')
lines=html.splitlines()
for n in range(625,642):
 if n<=len(lines): print(f'HTML {n}: {lines[n-1]}')
