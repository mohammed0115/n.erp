# إعداد بيانات الاختبار (Baseline Data Set)

هذا الملف يعرّف بيانات اختبار قياسية تُستخدم عبر جميع الـ Test Cases وUAT لضمان نتائج قابلة للمقارنة، وتسهيل تتبّع أثر المخزون/المال/التقارير.

## 1) قواعد عامة قبل البدء

- نفّذ الاختبارات على قاعدة بيانات **Testing** منفصلة (لا تستخدم بيانات تشغيل حقيقية).
- صفّر البيانات بين الجولات (Reset) لتفادي تلوث النتائج.
- ثبّت المنطقة الزمنية وتهيئة التاريخ على بيئة الاختبار (في المشروع يظهر `APP_TIMEZONE=Africa/Khartoum`).
- عطّل إرسال البريد/SMS في بيئة الاختبار أو وجّهها إلى Mailtrap.

## 2) مستخدمون وأدوار (Roles/Users)

أنشئ مستخدم لكل دور (بايميلات واضحة) لتطبيق سيناريوهات الصلاحيات:

- `admin1` (Admin) — صلاحيات كاملة.
- `mgr1` (Manager) — تقارير وتشغيل وإشراف.
- `acc1` (Accountant) — مدفوعات/حسابات/تقارير مالية فقط.
- `cash1` (Cashier) — POS + بيع + مرتجع بيع ضمن الصندوق.
- `wh1` (Warehouse User) — تحويل مخزني + تسويات + جرد (بدون بيع/شراء/أسعار).
- `pur1` (Purchasing Officer) — مشتريات + مرتجع مشتريات (بدون بيع).
- `sales1` (Sales User) — مبيعات (بدون تعديل تكلفة/جرد/تحويل).

ملاحظة تنفيذية: أسماء صلاحيات النظام تظهر بصيغة مثل `sales-add`, `purchases-edit`, `returns-index`… استخدم شاشة الأدوار/الصلاحيات لضبطها حسب سياسة شركتك.

## 3) إعدادات أساسية (Master Data)

### 3.1 المخازن (Warehouses)

- `WH1` — الاسم: `Main Warehouse`
- `WH2` — الاسم: `Branch Warehouse`

### 3.2 الفواتير/الجهة البائعة (Biller)

- `B1` — الاسم: `HQ Biller` (شركة/هاتف/عنوان مكتمل)

### 3.3 العملاء (Customers) + مجموعات العملاء

- مجموعة `Retail` — نسبة خصم: `0%`
- مجموعة `Wholesale` — نسبة خصم: `5%`

عملاء:
- `CASH` — `Cash Customer` (بدون حدود ائتمانية)
- `CREDIT` — `Credit Customer` (سيتم استخدامه في سيناريوهات الآجل/الذمم)

### 3.4 الموردون (Suppliers)

- `S1` — `Supplier One`
- `S2` — `Supplier Two` (لاختبار المقارنات/الاختلافات)

### 3.5 الضرائب (Taxes)

- `T0` — `No Tax` — `0%`
- `T15` — `VAT 15%` — `15%`

### 3.6 الوحدات (Units of Measure)

أنشئ Base Unit للقطعة ثم وحدات مشتقة:
- `PCS` — `Piece` (Base)
- `BOX10` — `Box (10 PCS)` (operator: `*`, operation value: `10`)

هدفها: اختبار تحويل الوحدات في البيع/الشراء/المرتجعات/التحويلات.

### 3.7 الحسابات (Accounts)

- `A_CASH` — `Cash` — اجعله Default.
- `A_BANK` — `Bank`

## 4) المنتجات القياسية للاختبار (Test Products)

أنشئ المنتجات التالية (الأسماء/الأكواد مقترحة — عدّل حسب واجهة النظام):

### P1 — منتج قياسي خاضع للضريبة
- النوع: `standard`
- الكود/SKU: `P-STD-TAX`
- الوحدة: `PCS` (شراء/بيع)
- التكلفة: `10.00`
- سعر البيع: `15.00`
- الضريبة: `VAT 15%`
- طريقة الضريبة: اختبر الحالتين (Inclusive/Exclusive إن وجدت في النظام)
- حد إعادة الطلب: `5`

### P2 — منتج قياسي بدون ضريبة
- الكود: `P-STD-NTX`
- التكلفة: `5.00`
- السعر: `8.00`
- الضريبة: `No Tax`

### P3 — منتج Variant (مثال: مقاسات)
- النوع: `standard` + `is_variant = true`
- الكود الأساسي: `P-VAR`
- المتغيرات: `S`, `M`, `L`
- Item Codes للمتغيرات: `P-VAR-S`, `P-VAR-M`, `P-VAR-L`
- Additional price للمتغيرات: `0`, `+1`, `+2` (لاختبار التسعير)

### P4 — منتج Combo (Bundle)
- النوع: `combo`
- الكود: `P-COMBO`
- مكونات: `P1 x2` + `P2 x1`
- سعر البيع: يفضّل تحديده أعلى من مجموع التكاليف لاختبار الربحية

### P5 — منتج Digital (غير مخزني)
- النوع: `digital`
- الكود: `P-DIGI`
- السعر: `20.00`
- تأكد أن المخزون لا يتأثر به

### P6 — منتج Inactive
- الكود: `P-INACT`
- اجعله `inactive` لاختبار منع البيع/الشراء/الظهور

### P7 — منتج عليه Promotion
- الكود: `P-PROMO`
- Promotion price: `9.99`
- Starting/Last date: ضمن فترة الاختبار

## 5) تهيئة المخزون الابتدائية (Opening Stock)

بدلاً من تعديل مباشر في قاعدة البيانات، استخدم **شراء** أو **إذن إضافة/تسوية** لضمان وجود حركة قابلة للتتبع:

اقتراح كميات ابتداءً:
- `WH1`: P1=50, P2=30, P3(S)=10, P3(M)=10, P3(L)=10
- `WH2`: P1=10, P2=10

## 6) استعلامات تحقق سريعة (SQL Sanity Checks) — اختياري

> نفّذها فقط على بيئة اختبار وبحذر.

- رصيد منتج على كل المخازن:
  - `SELECT warehouse_id, product_id, variant_id, qty FROM product_warehouse WHERE product_id = <ID>;`
- إجمالي رصيد المنتج (منتج):
  - `SELECT id, code, qty FROM products WHERE id = <ID>;`
- آخر فاتورة بيع/شراء:
  - `SELECT id, reference_no, sale_status, payment_status, grand_total, paid_amount FROM sales ORDER BY id DESC LIMIT 5;`
  - `SELECT id, reference_no, status, payment_status, grand_total, paid_amount FROM purchases ORDER BY id DESC LIMIT 5;`

