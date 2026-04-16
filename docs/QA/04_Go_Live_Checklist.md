# Go-Live Checklist - Sales + Purchases + Products + Inventory/Warehouses + POS

> هذا الـ Checklist هو بوابة قرار (Go/No-Go) قبل الإطلاق. أي بند غير مكتمل في الأقسام الحرجة يعني **تأجيل الإطلاق** أو وجود خطة معالجة (Mitigation) مكتوبة وموقعة من المالك التجاري والمدير التقني.

**مراجع التنفيذ**
- Must-Pass Scenarios: `docs/QA/03_Top_20_Critical_Scenarios.md`
- UAT Sign-off: `docs/QA/02_UAT_Cases.md`
- Master Test Cases: `docs/QA/01_Master_Test_Cases.md`

---

## 0) Go/No-Go Gate (حرج)
- [ ] جميع عناصر `03_Top_20_Critical_Scenarios.md` = PASS مع Evidence (Ref No + لقطة + تقرير مرتبط).
- [ ] لا توجد عيوب مفتوحة Severity = Critical/High (أو يوجد استثناء مكتوب وموقع + Risk acceptance).
- [ ] تطابق فاتورة/حركة/تقرير: لا يوجد أي mismatch بين (Sales/Purchases/Returns/Transfers) والتقارير.
- [ ] تم اختبار Backup + Restore فعلياً (قاعدة البيانات + المرفقات) على بيئة منفصلة.
- [ ] تم اعتماد مصفوفة الصلاحيات (Permission Matrix) وتطبيقها على الأدوار الفعلية.
- [ ] خطة Hypercare (7-14 يوم) + قناة بلاغات + SLA + مسؤوليات واضحة.

---

## A) Business Readiness (حرج)
- [ ] سياسة المخزون السالب: **ممنوع** أو **مسموح بضوابط** (صلاحية Override + سبب + تقرير + Audit).
- [ ] دورة الشراء واضحة: Ordered/Partial/Received (متى يدخل المخزون؟ من يعتمد؟).
- [ ] دورة البيع واضحة: Draft/Pending/Completed (هل Pending يحجز مخزون أم لا؟ قرار مكتوب).
- [ ] سياسة المرتجعات: (Refund نقدي / رصيد عميل / إشعار دائن) + حدود زمنية + صلاحيات.
- [ ] سياسة الخصومات: حدود الخصم لكل دور + خصم سطر/فاتورة + قبل/بعد الضريبة (قرار مكتوب).
- [ ] سياسة الإلغاء/الحذف: لا Hard delete للمستندات المحاسبية إلا بضوابط صارمة + سبب + Audit.
- [ ] سياسة التسعير: هل يوجد سعر حسب المخزن؟ وكيف يتم التعامل مع السعر الفارغ (NULL)؟
- [ ] سياسة التكلفة والربح: FIFO/AVG/آخر تكلفة؟ وكيف تتأثر التقارير عند تعديل cost بعد البيع؟

## B) Data Readiness (حرج)
- [ ] Master Data مكتمل: Products + Units + Taxes + Categories/Brands + Warehouses + Customers + Suppliers + Accounts.
- [ ] تفرد أكواد المنتجات/الباركود + تفرد أكواد الـ Variants (item_code) بدون استثناءات.
- [ ] الوحدات والتحويلات (operator '*' و '/') مختبرة على شراء/بيع/تحويل/مرتجع.
- [ ] Opening stock لكل مخزن عبر حركة قابلة للتتبع (Purchase/Adjustment) وليس تعديل مباشر في DB.
- [ ] فحص تكرار العملاء/الموردين (اسم/هاتف/رقم ضريبي إن وجد) ومعالجة الدمج قبل الإطلاق.
- [ ] توثيق Mapping للبيانات القديمة -> الجديدة + نتائج Migration + نسب نجاح/فشل الصفوف.
- [ ] عينة جرد واقعي قبل الإطلاق (20-50 صنف) وتطابقها مع تقرير Warehouse stock.

## C) Configuration & Environment (حرج)
- [ ] إعدادات Laravel للإنتاج: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` صحيح, `APP_KEY` فريد.
- [ ] متغيرات النظام المطلوبة ليست بوضع Demo: `USER_VERIFIED=1` (لتجنب تعطيل خصائص حساسة).
- [ ] إعدادات الوقت/التاريخ: Timezone موحد بين السيرفر وأجهزة POS + date format معتمد.
- [ ] العملة + عدد الخانات العشرية (مثلاً 2) + سياسة rounding متفق عليها.
- [ ] وجود حساب افتراضي للمدفوعات (Default account) + تحقق عدم وجود Null يسبب كسر أثناء الدفع/المرتجع.
- [ ] إعدادات POS: قالب الإيصال + شعار/عنوان + إعداد الطابعة/حجم الورق + اختبار طباعة فعلي.
- [ ] تفعيل/تعطيل وسائل الدفع غير المستخدمة (Stripe/PayPal/Gift Card) لتقليل المخاطر.

## D) Roles & Permissions (حرج)
- [ ] إنشاء أدوار واضحة: Admin/Manager/Accountant/Cashier/Warehouse/Purchasing/Sales.
- [ ] Least privilege: الكاشير لا يرى cost/profit ولا يغير الأسعار/الضرائب/التكلفة.
- [ ] منع الوصول المباشر عبر URL لأي شاشة/عملية بدون صلاحية (Authorization حقيقي وليس فقط UI).
- [ ] staff_access = own (إن كان مطلوباً) يعمل فعلياً ويمنع رؤية مستندات الآخرين (No IDOR).
- [ ] فصل المهام (Segregation of duties): من ينشئ لا يعتمد دائماً (خصوصاً التسويات/الإلغاء/Refund).
- [ ] سياسة كلمات المرور + تعطيل المستخدمين غير النشطين + حسابات مشاركة (Shared) ممنوعة.

## E) Financial Controls (حرج)
- [ ] اختبار Split payments وPartial payments على سيناريوهات واقعية (Cash + Card + Deposit إن وجد).
- [ ] اختبار Refunds/Returns وتأثيرها على الصندوق والتقارير (Sales Return يظهر في Cash Register).
- [ ] معادلة الصندوق صحيحة: Total cash = Opening + Payments - (Returns + Expenses) على عينات.
- [ ] فتح/إغلاق الوردية: لا يسمح بوجود أكثر من وردية مفتوحة لنفس المستخدم/المخزن (أو سياسة واضحة).
- [ ] مستندات الإلغاء/المرتجع/التسوية تحتوي سبب (Reason) أو يوجد إجراء بديل مُعتمد ومكتوب.
- [ ] منع خصم أكبر من قيمة السطر/الفاتورة + منع ضريبة/سعر سالب.

## F) Inventory Controls (حرج)
- [ ] تطابق المخزون: `products.qty` == SUM(`product_warehouse.qty`) للمنتجات المخزنية (عينة + بعد سيناريوهات).
- [ ] البيع المكتمل يخصم المخزون (Standard/Variant/Combo) مرة واحدة فقط.
- [ ] Draft/Pending (إن كان موجود) لا يخصم/لا يحجز إلا إذا كان ذلك مطلوباً ومطبقاً بوضوح.
- [ ] Transfer: Completed/Cancel/Delete يعكس بدقة (خصوصاً مع وحدات التحويل operator '/').
- [ ] Adjustment: لا يسمح بالسالب بدون صلاحية + سبب + Audit + تقرير تسويات.
- [ ] Stock Count CSV: مسار كامل مجرب (Create -> Finalize -> Qty adjustment) بدون أخطاء ربط.

## G) Reporting & Reconciliation (حرج)
- [ ] Sales report يطابق مجموع فواتير sales للفترة (مع استبعاد Draft).
- [ ] Purchase report يطابق purchases + يظهر مرتجعات المشتريات بشكل صحيح.
- [ ] تقرير الضرائب يطابق الفواتير (نفس rounding والسياسة قبل/بعد الخصم).
- [ ] Profit/Loss منطقي على Dataset معروف (شراء ثم بيع ثم مرتجع) ومفهوم للإدارة.
- [ ] تقرير Warehouse stock يطابق أرصدة المخازن (WH1/WH2) بعد Transfer/Return/Adjustment.
- [ ] تقرير الصندوق/الكاشير يطابق payments/returns/expenses لنفس الوردية.

## H) POS Operations (حرج)
- [ ] اختبار أجهزة: Barcode scanner + Receipt printer + Cash drawer (إن وجد).
- [ ] سلوك المسح المتكرر واضح (Duplicate scan): يزيد الكمية أو يكرر السطر بشكل ثابت ومتوقع.
- [ ] اختبار سرعة POS تحت ضغط (>= 50 عملية متتالية) بدون timeouts أو تجميد.
- [ ] انقطاع الشبكة أثناء الحفظ: لا Double submit ولا فقدان صامت (يجب رسالة واضحة/حفظ Draft).
- [ ] Session timeout: لا يفقد الكاشير الطلب؛ يوجد تنبيه/حفظ مسودة.

## I) Security & Deployment (حرج)
- [ ] HTTPS مفعل + Cookies آمنة (Secure/HttpOnly/SameSite) + جلسات صحيحة.
- [ ] مرفقات وUploads: منع التنفيذ + أسماء ملفات فريدة (لا overwrite) + مسارات وصلاحيات صحيحة.
- [ ] مجلدات الكتابة موجودة وقابلة للكتابة (حسب النظام): `public/sale/documents`, `public/documents/purchase`, `public/documents/transfer`, `public/return/documents`, `public/stock_count`.
- [ ] صلاحيات DB: مستخدم قاعدة بيانات غير root في الإنتاج + أقل صلاحيات مطلوبة.
- [ ] نسخ احتياطي يومي (DB + uploads) + اختبار Restore فعلي + توثيق خطوات الاسترجاع.
- [ ] Logging/Monitoring: تتبع أخطاء التطبيق + إنذارات مساحة DB/Storage + تدوير السجلات.

## J) Cutover Plan (حرج)
- [ ] تحديد تاريخ/ساعة التحويل (Cutover) + خطة Rollback مكتوبة ومجربة (على الأقل نظرياً).
- [ ] Freeze لبيانات النظام القديم قبل النقل + نقطة مرجعية (Snapshot) موثقة.
- [ ] تشغيل جرد/تقرير سريع بعد النقل (عينات + تقارير) قبل فتح البيع.
- [ ] خطة دعم بعد الإطلاق (Hypercare) + فريق مناوبة + طريقة تصعيد (Escalation).

## K) Production Smoke Test (بعد النشر مباشرة)
- [ ] تسجيل دخول (Admin/Cashier) + فتح POS بدون أخطاء.
- [ ] فتح وردية (Cash Register) ثم بيع POS بسيط + طباعة إيصال.
- [ ] مرتجع بيع بسيط + التحقق من تأثيره على الصندوق.
- [ ] تحويل مخزني بسيط WH1 -> WH2 + التحقق من أرصدة المخازن.
- [ ] استخراج تقرير مبيعات/مخزون/ضرائب للفترة الحالية والتأكد من ظهوره بدون أخطاء.
