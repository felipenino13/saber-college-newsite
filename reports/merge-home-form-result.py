import json
from pathlib import Path
p=Path('reports')
interaction=json.load(open(p/'home-form-style-fix-result-20261016.json',encoding='utf-8'))
struct=json.load(open(p/'home-form-style-structural-20261016.json',encoding='utf-8'))
comparison=json.load(open(p/'home-form-style-after-20261016.json',encoding='utf-8'))
interaction['diagnosis']={
 'cause':'Copied CSS remained scoped to .elementor-2015 and form widget f698ff6, while the home renders as .elementor-7 with form widget b7159a9.',
 'replacedPageScopeSelectors':30,
 'replacedFormSelectors':28,
 'homeStyleWidgetId':'8d28c13'
}
interaction['referenceComparison']={
 'referencePageId':872,'referenceTemplateId':2015,
 'homeInput':comparison['home']['input'],'referenceInput':comparison['pta']['input'],
 'homeRadioGroup':comparison['home']['radioGroup'],'referenceRadioGroup':comparison['pta']['radioGroup'],
 'homeRadioLabel':comparison['home']['radioLabel'],'referenceRadioLabel':comparison['pta']['radioLabel'],
 'homeSubmitButton':comparison['home']['button'],'referenceSubmitButton':comparison['pta']['button']
}
interaction['structuralVerification']=struct
interaction['implementation']={'localOnly':True,'productionPublished':False,'formSubmitted':False,'idempotentSecondRun':True,'backup':'reports/backups/home-form-style-20261016/','containerBackup':'/tmp/home-form-style-before-20261016-132225.json'}
(p/'home-form-style-fix-result-20261016.json').write_text(json.dumps(interaction,ensure_ascii=False,indent=2),encoding='utf-8')
