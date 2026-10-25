from pathlib import Path
src=Path('scripts/redesign-pnp-landing.php').read_text(encoding='utf-8')
repl={
'1640':'1641','/lp/pnp/':'/lp/pta/','page 1640':'page 1641','pnp-landing':'pta-landing','PNP landing':'PTA landing','e-pnp-':'e-pta-','saber-pnp-':'saber-pta-','saber-pnp-image-':'saber-pta-image-',
'48a1cbd':'0dd4331','03806ea':'3f76910','28a9a12':'39137a3','2fc57b9':'3aa72ef',
'9969a67':'8fc02ba','52a4ab4':'8847a87','8537981':'3bebbc2','8ee6cf7':'a09c2d7','7946335':'a5d0a41',
'444007b':'75058b6','476d26c':'dd13216','d58182d':'c2e856b',
'8b72009':'803a34d','72af871':'67b51d4','e5a03b9':'4f59fcc','9cbf9e5':'277d7e5','858052f':'44c0a60','79822df':'45dcb10','f3efa42':'54ff074',
'8f8dcf0':'a51052d','439daea':'b99e4c8','b7c630b':'f212495','f3ef365':'a2fbf3b','ff8245a':'7e14622','12b4699':'df4ae08','0e78424':'a713170','af9f3a0':'fd7ba6c','16f58fe':'dfe59a1','10aeef6':'39a6777',
'556267d':'ace8006','dd0a99f':'4158399','f0f682a':'35cd6bc','07ead1f':'6e8008a','7bf770b':'281741e','c393672':'4969bb0','e4f204c':'ce748e1','dfe5bad':'ee6d4c6',
'e5cf091':'567ac95','f5b9dbd':'b27f293','dd8f8c0':'1cbd84d','7a84349':'6774f9e','597811a':'80ce3c8','0455e19':'078354f',
'917f4cc':'4a8425f','278e2bb':'9b1cde9','c1f40e5':'a91de85','b66320f':'000cd6a','a0ade2c':'d567f79','cd0b386':'56be9ec',
'997df28':'8731652','581c657':'4b939ae','1802a1c':'9c89c36','a0523e2':'ee21599','9aeb4fc':'a439134','809f889':'3cc86c6','62145f2':'6e05f1b','dac6d50':'b661da2',
'964cf10':'fe66876','f0b0470':'62ca067','45fe7f9':'791ea1b','86de24b':'7d02d46','5f14145':'3a0c69c','a3ff76f':'1da6dd1',
'1652':'1663','1659':'1664','1660':'1666','1661':'1665',
'Nursing students practicing clinical skills':'Physical therapist assistant supporting a patient during mobility exercise',
'Nursing students learning together':'Physical therapist assistant helping a patient practice walking',
'SABER College nursing student':'Physical therapist assistant guiding a patient through a resistance exercise',
'Nursing students at SABER College':'Physical therapist assistant working with a pediatric patient',
'career-nurses':'career-therapy','curriculum-student':'curriculum-therapy','overview-students':'overview-therapy','hero-training':'hero-therapy',
}
for a,b in repl.items(): src=src.replace(a,b)
src=src.replace("$page->post_name !== 'pnp'", "$page->post_name !== 'pta'")
# Rewrite required widget block completely.
start=src.index('$required = [')
end=src.index('];',start)+2
required="""$required = [
    '0dd4331', '3f76910', '39137a3', '3aa72ef',
    '8fc02ba', '8847a87', '3bebbc2', 'a09c2d7', 'a5d0a41',
    '75058b6', 'dd13216', '41ad152', 'c2e856b',
    '803a34d', '67b51d4', '4f59fcc', '277d7e5', '44c0a60', '45dcb10', '54ff074',
    'a51052d', 'b99e4c8', 'f212495', 'a2fbf3b', '7e14622', 'df4ae08', 'a713170', 'fd7ba6c', 'dfe59a1', '39a6777',
    'ace8006', '4158399', '35cd6bc', '6e8008a', '281741e', '4969bb0', 'ce748e1', 'ee6d4c6',
    '567ac95', 'b27f293', '1cb2e88', '1cbd84d', '6774f9e', '80ce3c8', '078354f',
    '4a8425f', '9b1cde9', 'a91de85', '000cd6a', 'd567f79', '56be9ec',
    '8731652', '4b939ae', '9c89c36', 'ee21599', 'a439134', '3cc86c6', '6e05f1b', 'b661da2',
    'fe66876', '62ca067', '791ea1b', '7d02d46', '3a0c69c', '1da6dd1',
];"""
src=src[:start]+required+src[end:]
# Replace image alt map and attachment validation list after broad substitutions.
a=src.index('$image_alts = ['); b=src.index('];',a)+2
alts="""$image_alts = [
    1663 => 'Physical therapist assistant supporting a patient during mobility exercise',
    1664 => 'Physical therapist assistant helping a patient practice walking',
    1666 => 'Physical therapist assistant guiding a patient through a resistance exercise',
    1665 => 'Physical therapist assistant working with a pediatric patient',
    1662 => 'SABER College',
    1651 => 'SABER College accreditation',
];"""
src=src[:a]+alts+src[b:]
# Replace attachment validation foreach.
old="foreach ([1663, 1664, 1666, 1665, 1662, 1651] as $attachment_id)"
src=src.replace(old, old)
# New extra PTA body IDs.
src=src.replace("'39137a3', '3bebbc2', 'dd13216',", "'39137a3', '3bebbc2', 'dd13216', '41ad152',")
src=src.replace("'b27f293', '1cbd84d',", "'b27f293', '1cb2e88', '1cbd84d',")
# Remove obsolete fourth curriculum headings/bodies and fourth FAQ remnants left unmapped.
src=src.replace(", '85b34c3', '8d6539a'", "")
src=src.replace(", '923df6d'", "")
src=src.replace(", '9912d24', '899a2cd'", "")
src=src.replace("    ['85b34c3', '8d6539a'],\n", "")
# Add second overview paragraph.
src=src.replace("[$widgets['75058b6'], $widgets['dd13216'], $cta_overview]", "[$widgets['75058b6'], $widgets['dd13216'], $widgets['41ad152'], $cta_overview]")
# Add second career paragraph.
src=src.replace("$widgets['567ac95'], $widgets['b27f293'], $widgets['1cbd84d']", "$widgets['567ac95'], $widgets['b27f293'], $widgets['1cb2e88'], $widgets['1cbd84d']")
# Replace FAQ pairs, because PTA has three questions with different answer counts.
fa=src.index('$faq_pairs = ['); fb=src.index('];',fa)+2
faq="""$faq_pairs = [
    ['4b939ae', ['9c89c36']],
    ['ee21599', ['a439134', '3cc86c6']],
    ['6e05f1b', ['b661da2']],
];"""
src=src[:fa]+faq+src[fb:]
# Full-width final odd FAQ card.
src=src.replace("'width' => $size(48.9, '%'),\n        'gap' => $size(9),", "'width' => $size((count($faq_pairs) % 2 === 1 && $index === count($faq_pairs) - 1) ? 100 : 48.9, '%'),\n        'gap' => $size(9),",1)
# PTA media and reported IDs.
src=src.replace("'media_ids' => [1663, 1664, 1666, 1665, 1662, 1651]", "'media_ids' => [1663, 1664, 1666, 1665, 1662, 1651]")
Path('scripts/redesign-pta-landing.php').write_text(src,encoding='utf-8')
print('wrote',len(src),'bytes')
