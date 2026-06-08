import fitz, os

out_dir = r'D:\курсовая\pvp\extracted_images'
for fname in ['BPMN.pdf', 'Use-Case.pdf']:
    doc = fitz.open(os.path.join(r'D:\курсовая', fname))
    print(f'{fname}: {len(doc)} pages')
    for i, page in enumerate(doc):
        pix = page.get_pixmap(dpi=200)
        base = fname.replace('.pdf', '')
        out = os.path.join(out_dir, f'{base}_page{i+1}.png')
        pix.save(out)
        print(f'  Saved page {i+1}: {out} ({pix.width}x{pix.height})')
    doc.close()
