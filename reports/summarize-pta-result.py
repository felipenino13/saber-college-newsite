import json,re,html,hashlib
from pathlib import Path
from collections import Counter
base=Path('reports')
before=json.load(open(base/'backups/pta-landing-redesign-20261014/elementor-before.json',encoding='utf-8-sig'))
after=json.load(open(base/'backups/pta-landing-redesign-20261014/elementor-after.json',encoding='utf-8-sig'))

def walk(xs):
    for x in xs:
        if not isinstance(x,dict): continue
        yield x
        yield from walk(x.get('elements',[]))

def visible(v):
    s=re.sub(r'<[^>]+>',' ',str(v))
    return ' '.join(html.unescape(s).replace('\u200b','\u200b').split()).strip()

def texts(data,exclude_marker=False):
    out=[]
    for x in walk(data):
        if x.get('elType')!='widget': continue
        if exclude_marker and x.get('id')=='791ea1b': continue
        settings=x.get('settings',{}) if isinstance(x.get('settings'),dict) else {}
        for key in ('title','editor','text','button_text'):
            if settings.get(key):
                t=visible(settings[key])
                if t: out.append(t)
    return out
orig=sorted(set(texts(before,True)))
new=set(texts(after))
missing=[x for x in orig if x not in new]
el=Counter(); widgets=Counter()
for x in walk(after):
    el[x.get('elType','unknown')]+=1
    if x.get('widgetType'): widgets[x['widgetType']]+=1
qa=json.load(open(base/'pta-landing-redesign-result-20261014.json',encoding='utf-8'))
qa['elementorStructure']={'topLevelSections':len(after),'elementTypes':dict(el),'widgetTypes':dict(widgets),'nativeEditable':widgets.get('html',0)==0 and el.get('e-flexbox',0)>0}
qa['contentPreservation']={'originalUniqueVisibleStrings':len(orig),'missingStrings':missing,'removedEditorialMarker':'// Eliminar'}
qa['implementation']={'pageId':1641,'localOnly':True,'publishedToProduction':False,'convertedCtas':4,'formSubmitLabel':'Send My Request','formFieldIdsUnique':True,'mediaIds':[1663,1664,1666,1665,1662,1651],'designAssetIds':[2064,2065],'idempotentSecondRun':True,'elementorDataSha256':hashlib.sha256((base/'backups/pta-landing-redesign-20261014/elementor-after.json').read_bytes()).hexdigest()}
(base/'pta-landing-redesign-result-20261014.json').write_text(json.dumps(qa,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'elementTypes':dict(el),'widgetTypes':dict(widgets),'orig':len(orig),'missing':missing},ensure_ascii=False,indent=2))
