import json,re,sys
from pathlib import Path
script=Path(sys.argv[1]).read_text(encoding='utf-8')
data=json.load(open(sys.argv[2],encoding='utf-8'))
ids=set()
def walk(xs):
 for x in xs:
  if isinstance(x,dict):
   if x.get('id'): ids.add(x['id'])
   walk(x.get('elements',[]))
walk(data)
refs=set(re.findall(r"\$widgets\['([0-9a-f]+)'\]",script))
missing=sorted(refs-ids)
print('widget refs',len(refs),'source ids',len(ids),'missing',missing)
old=['pnp','8731663','85b34c3','8d6539a','4bf2df7','9912d24','899a2cd','923df6d']
for x in old:
 m=[i+1 for i,l in enumerate(script.splitlines()) if x in l.lower()]
 if m: print('old token',x,'lines',m)
