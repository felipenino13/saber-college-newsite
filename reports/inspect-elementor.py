import json, sys
from collections import Counter
sys.stdout.reconfigure(encoding='utf-8')
d=json.load(open(sys.argv[1], encoding='utf-8'))
if isinstance(d, dict):
    print('TOP_DICT_KEYS', list(d.keys()))
    for k,v in d.items():
        if isinstance(v,list):
            print('USING_LIST_KEY',k,'LEN',len(v)); d=v; break
print('TOP_TYPE',type(d).__name__,'ROOTS',len(d))
c=Counter(); w=Counter()
def walk(xs, depth=0):
    for x in xs:
        if not isinstance(x,dict):
            print('  '*depth+'NON_DICT '+repr(x)[:100]); continue
        c[x.get('elType','?')] += 1
        if x.get('widgetType'): w[x['widgetType']] += 1
        s=x.get('settings',{})
        if not isinstance(s,dict): s={}
        title=s.get('title') or s.get('editor') or s.get('text') or s.get('button_text') or ''
        title=' '.join(str(title).replace('<',' <').split())[:220]
        print('  '*depth + '{} {} {} | {}'.format(x.get('id'), x.get('elType'), x.get('widgetType',''), title))
        walk(x.get('elements',[]), depth+1)
walk(d)
print('ELTYPES', dict(c))
print('WIDGETS', dict(w))
