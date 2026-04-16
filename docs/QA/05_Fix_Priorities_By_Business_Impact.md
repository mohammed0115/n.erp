# جدول أولويات الإصلاح حسب Business Impact

> الهدف: ترتيب الإصلاحات بحيث تُغلق مخاطر **المال + المخزون + التقارير + الصندوق + الأمان** أولاً.

## تعريف الأولويات

- **P0 (Stop Go-Live):** يسبب خسارة مالية/فساد مخزون/تسريب بيانات/تلاعب أو يجعل الأرقام غير قابلة للثقة.
- **P1 (Fix قبل Go-Live إن أمكن):** لا يسبب انهيار مباشر لكنه يرفع المخاطر التشغيلية أو يعيق اعتماد الإدارة.
- **P2 (Post Go-Live سريع):** تحسينات مهمة لكن يمكن التعايش معها أيامًا مع إجراءات تشغيلية بديلة.
- **P3 (Backlog):** تحسينات UX/تقارير إضافية/كماليّات.

---

## جدول الأولويات (قابل للتحويل إلى Jira/Linear)

| Priority | المشكلة (Class) | أمثلة أعراض | Business Impact | كيف تُكتشف | توصية إصلاح (مختصر) | Owner |
|---|---|---|---|---|---|---|
| P0 | **Negative stock / Overselling** | بيع/تحويل/تسوية تجعل qty سالب | خسارة + عجز مخزون + قرارات خاطئة | UAT-16 + TC-SALE-004 | تحقق قبل الخصم + قفل/صلاحية Override + Audit | Backend + DB |
| P0 | **Invoice <-> Stock mismatch** | فاتورة مكتملة لا تخصم/لا تعكس عند الحذف | فساد مخزون + إغلاق حسابات مستحيل | TC-SALE-001/015 | توحيد منطق الحركات + اختبارات Reversal + قيود DB | Backend |
| P0 | **Totals/tax/discount wrong** | grand_total لا يساوي مجموع السطور | خسائر/خلافات ضريبية | TC-PUR-004/TC-SALE-011 | إعادة الحساب على السيرفر + توحيد rounding | Backend |
| P0 | **Price/discount tampering (server trust issue)** | تغيير قيم POST يغيّر السعر | احتيال مالي | TC-SEC-004 | Ignore client totals؛ احسب من DB + validate ranges | Backend |
| P0 | **IDOR / Direct URL access** | مستخدم يرى فواتير غيره | تسريب بيانات + مخالفة | TC-SEC-003 | Authorization policy لكل action + scoping by warehouse/user | Backend |
| P0 | **Cash register inconsistency** | Total cash لا يطابق المعادلة | فروقات صندوق | UAT-23/25 | enforce single open register + reconcile fields + reports | Backend + Product |
| P0 | **Duplicate transaction (double submit)** | فاتورة/دفعة تتكرر | تضخيم إيراد/مخزون | TC-PUR-007/TC-SALE-005 | Idempotency keys/unique reference + UI disable submit | Frontend + Backend |
| P0 | **Transfer reversal wrong** | Delete/Cancel transfer لا يعكس صح | اختلاف أرصدة بين مخازن | TC-INV-004 | إصلاح حساب الكمية + اختبارات وحدات operator '/' و '*' | Backend |
| P0 | **Atomicity/Transactions** | فشل منتصف الحفظ يترك بيانات نصفية | فساد بيانات صعب الإصلاح | أثناء ضغط/انقطاع | لفّ عمليات (Sale/Purchase/Return/Transfer) داخل DB transaction + locking | Backend + DB |
| P1 | **Audit trail ناقص** | لا يوجد سبب/من نفّذ الإلغاء/التسوية | صعوبة تحقيق + مخالفات | مراجعة UAT | أضف fields: reason, approved_by/cancelled_by + logs immutable | Backend + Product |
| P1 | **Hard delete للمستندات** | حذف يمسح التاريخ | مراجعة/تدقيق صعب | أثناء UAT | Soft delete + “Cancel” بدل حذف + منع حذف بعد اعتماد | Backend + Product |
| P1 | **Data types المالية (double/string)** | فروقات rounding/تقارير | أخطاء ضريبية/ربح | TC-PROD-013 | تحويل للأعمدة DECIMAL + migration + backfill + tests | DB + Backend |
| P1 | **Warehouse-specific price gaps** | سعر غلط حسب المخزن | تسعير خاطئ | UAT-17 | fallback logic واضح + منع NULL في price أو UI rule | Backend + Frontend |
| P1 | **File upload overwrite / unsafe filenames** | مرفقات تختفي/تستبدل | فقد مستندات + خطر أمني | TC-PUR-013/SEC-007 | generate unique filenames + storage خارج public + validation stricter | Backend + DevOps |
| P1 | **Permissions granularity** | كاشير يقدر يغيّر تكلفة/يحذف | احتيال/أخطاء تشغيل | UAT-24 | مراجعة Matrix صلاحيات + deny by default + tests | Product + Backend |
| P1 | **Stock count CSV injection** | فتح CSV يحقن صيغ Excel | خطر على أجهزة الإدارة | مراجعة أمن | sanitize CSV (prefix apostrophe) + validate content | Backend |
| P2 | **UX: رسائل أخطاء غير واضحة** | المستخدم لا يفهم سبب الرفض | أخطاء تشغيل | أثناء UAT | تحسين رسائل validation + حالات المستند (Draft/Completed) | Frontend |
| P2 | **Performance في POS** | بطء عند البحث/الحفظ | طوابير كاشير | ضغط عملي | indexing + caching + تحسين queries + pagination | Backend + DB |
| P3 | **تقارير إضافية/تخصيصات** | تقارير غير موجودة/تحسينات | تحسين إداري | طلبات الإدارة | backlog بعد الاستقرار | Product |

---

## ملاحظة تنفيذية

لتحويل هذا الجدول إلى خطة عمل:
1) اربط كل بند بـ **Test Case ID** من `docs/QA/01_Master_Test_Cases.md`.
2) عرّف “Definition of Done” = Pass في الـ Test Case + Evidence.
