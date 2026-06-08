#!/usr/bin/env python3
"""Verify final DOCX state."""
import json
from docx import Document

doc = Document(r'D:\курсовая\итоговая_отформатированная_версия_ИСПРАВЛЕННАЯ.docx')
paras = [{'idx': i, 'text': p.text.strip()[:120], 'style': p.style.name} for i, p in enumerate(doc.paragraphs) if p.text.strip()]

with open('verified_final2.json', 'w', encoding='utf-8') as f:
    json.dump(paras, f, ensure_ascii=False, indent=1)

print('Total paras:', len(paras))

checks = [
    '1.5. Модели',
    '1.6. Модель вариантов использования',
    'Таблица 7',
    '2.8. Хранимые процедуры и триггеры',
    '3.2.',
    '3.4.',
    '2.3.1. Диаграмма логической модели',
    '2.4.2. Диаграмма физической модели',
]

for c in checks:
    found = [(p['idx'], p['text'][:80]) for p in paras if c in p['text']]
    if found:
        for idx, txt in found:
            print(f'  OK [{idx}] {txt}')
    else:
        print(f'  MISSING: {c}')
