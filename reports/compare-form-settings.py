import json,sys
sys.stdout.reconfigure(encoding='utf-8')
def find(d,id):
 for x in d:
  if not isinstance(x,dict): continue
  if x.get('id')==id:return x
  y=find(x.get('elements',[]),id)
  if y:return y
home=json.load(open('reports/backups/home-form-style-20261016/elementor-7-before.json',encoding='utf-8-sig'))
ref=json.load(open('reports/backups/home-form-style-20261016/elementor-2015-reference.json',encoding='utf-8-sig'))
a=find(home,'b7159a9')['settings'];b=find(ref,'f698ff6')['settings']
for k in sorted(set(a)|set(b)):
 if a.get(k)!=b.get(k) and k not in ('form_fields','email_to','email_to_2','email_from','email_from_2','email_subject','email_subject_2','email_content','email_content_2','redirect_to','submit_actions'):
  print(k,'\n HOME=',repr(a.get(k))[:700],'\n REF =',repr(b.get(k))[:700])
