# Master Test Cases — Sales / Purchases / Products / Inventory / POS

> الهدف: تغطية عملية وشاملة (Functional + Business Flow + Validation + Integrity + Financial + Permissions + Reporting + Security) بنمط **قابل للتنفيذ** على النظام الحالي.
>
> مرجع بيانات موحدة للاختبارات: راجع `docs/QA/00_Test_Data_Setup.md`.

---

## ملاحظات تنفيذية عامة (تطبق على كل Test Case)

- **إثباتات DB (Evidence):** في الحالات الحساسة (مخزون/مال/تقارير) لا تكتفِ بنتيجة الواجهة. خذ لقطات/Export أو نفّذ استعلامات تحقق (sanity SQL).
- **منع التلوث:** لا تعيد استخدام نفس المستندات (Sales/Purchase/Transfer) بين Test Cases إلا إذا كان ذلك ضمن نفس السيناريو.
- **التحقق من الاسترجاع (Reversal):** أي Delete/Cancel/Update يجب أن يعكس أثره على:
  - `products.qty`
  - `product_warehouse.qty`
  - المدفوعات `payments`
  - التقارير المستخرجة من النظام
- **مخاطر معروفة بالتصميم:** لا يوجد مفهوم “Reserved Stock” صريح؛ لذلك حالات Draft/Pending لا يجب اعتبارها حجزًا للمخزون إلا إذا كانت هناك شاشة/منطق لذلك.

---

## A) Products — المنتجات

### TC-PROD-001 — إنشاء منتج Standard كامل البيانات
- **Priority:** High
- **Preconditions:** صلاحية `products-add`
- **Steps:**
  1) Products → Add New
  2) أدخل: Name, Code=`P-STD-TAX`, Type=`standard`, Unit=`PCS`, Purchase Unit=`PCS`, Sale Unit=`PCS`, Cost=`10.00`, Price=`15.00`, Tax=`VAT 15%`, Alert Qty=`5`, Active=Yes
  3) Save
- **Expected:**
  - المنتج يظهر في قائمة المنتجات
  - يمكن البحث عنه بالكود والاسم
  - لا يوجد أي رصيد في `product_warehouse` قبل تنفيذ شراء/إضافة مخزون
- **DB Checks (اختياري):**
  - `SELECT code, cost, price, qty, is_active FROM products WHERE code='P-STD-TAX';`

### TC-PROD-002 — منع تكرار Code (SKU/Barcode) لمنتج Active
- **Priority:** Critical
- **Steps:**
  1) أنشئ المنتج `P-STD-TAX` (أو استخدم الموجود)
  2) حاول إنشاء منتج جديد بنفس `code`
- **Expected:** رفض الحفظ برسالة واضحة (Duplicate code).
- **Business Impact:** يمنع فوضى الباركود/الفوترة/التقارير.

### TC-PROD-003 — السماح بتكرار Code إذا كان المنتج القديم Inactive (إن كان هذا المطلوب)
- **Priority:** Medium
- **Steps:**
  1) اجعل المنتج `P-INACT` Inactive
  2) أنشئ منتج جديد بنفس الكود
- **Expected:** حسب سياسة العمل:
  - إما يُمنع التكرار دائمًا (مفضل ERP)، أو
  - يُسمح فقط إذا كان القديم inactive (لكن يجب توثيق ذلك + تحذير)
- **ملاحظة QA:** إذا سُمح بالتكرار، اختبر أثره على البحث/التقارير/الباركود.

### TC-PROD-004 — إنشاء منتج Variant + أكواد متغيرات فريدة
- **Priority:** High
- **Steps:**
  1) Products → Add → فعّل خيار Variant
  2) أنشئ `P-VAR` مع variants (`S/M/L`) وItem Codes (`P-VAR-S/M/L`)
  3) Save
- **Expected:**
  - المتغيرات تظهر في POS وSales search
  - لا يوجد تعارض في item_code
  - الرصيد لكل variant يبدأ من 0

### TC-PROD-005 — منع تكرار Item Code للـ Variant
- **Priority:** High
- **Steps:** أنشئ Variant جديد بـ item_code موجود مسبقًا
- **Expected:** منع الحفظ أو رسالة تحذير تمنع ازدواج الباركود للمتغيرات.

### TC-PROD-006 — إنشاء Combo product وتأثيره على المخزون عند البيع/المرتجع
- **Priority:** Critical
- **Steps:**
  1) أنشئ Combo `P-COMBO` مكوّن من `P1 x2` + `P2 x1`
  2) نفّذ بيع مكتمل للـ Combo
- **Expected:** لا يُخصم رصيد `P-COMBO` نفسه، بل يُخصم من مكونات الكومبو (P1/P2) بمضاعفات صحيحة.

### TC-PROD-007 — منتج Digital لا يؤثر على المخزون
- **Priority:** High
- **Steps:** أنشئ `P-DIGI` ثم نفّذ بيع مكتمل
- **Expected:** لا تغيير في `products.qty` ولا `product_warehouse.qty` لهذا المنتج.

### TC-PROD-008 — Promotion pricing ضمن المدة
- **Priority:** High
- **Steps:**
  1) أنشئ `P-PROMO` مع Promotion price وتواريخ صالحة
  2) ابحث عنه في POS/بيع
- **Expected:** السعر المستخدم هو Promotion price داخل المدة، ويعود للسعر الأساسي خارجها.

### TC-PROD-009 — Tax method (Inclusive/Exclusive) إن وجدت
- **Priority:** High
- **Steps:** أنشئ منتجين بنفس السعر أحدهما Tax Exclusive والآخر Inclusive ثم نفّذ بيع
- **Expected:** إجمالي الضريبة وGrand total يحسبان بشكل صحيح حسب الطريقة.

### TC-PROD-010 — Import Products (Bulk) مع صفوف غير صالحة
- **Priority:** High
- **Steps:**
  1) استخدم شاشة Import
  2) ملف يحتوي صفوف: code مكرر، سعر نصي، ضريبة غير موجودة، وحدة غير موجودة
- **Expected:** 
  - رفض الصفوف غير الصالحة مع تقرير واضح
  - عدم إدخال جزئي “صامت” يسبب فساد بيانات

### TC-PROD-011 — Inactive product لا يظهر في POS / لا يُباع
- **Priority:** Critical
- **Steps:** اجعل `P-INACT` inactive ثم حاول بيعه في POS
- **Expected:** المنتج لا يظهر/لا يمكن إضافته؛ وإذا أُدخل باركوده يدويًا يجب رفضه.

### TC-PROD-012 — طباعة Barcode
- **Priority:** Medium
- **Steps:** Products → Print barcode للمنتج/variant
- **Expected:** باركود صحيح + لا يتغير عند تحديث بيانات غير الكود.

### TC-PROD-013 — حقول مالية بصيغة Decimal (سعر/تكلفة) + تقريب
- **Priority:** High
- **Steps:** أدخل أسعار مثل `9.99`, `0.01`, `1000000.99`
- **Expected:** حفظ/عرض/حسابات بدون كسور عائمة خاطئة أو تقريبات غير مبررة.

### TC-PROD-014 — حذف/تعطيل منتج مستخدم في حركات
- **Priority:** Critical
- **Steps:** استخدم المنتج في شراء/بيع ثم حاول حذفه
- **Expected:** 
  - لا يسمح بحذف Hard delete إذا سيكسر التاريخ المحاسبي
  - يسمح فقط بالتعطيل (Soft)

### TC-PROD-015 — تعديل Cost بعد بيع سابق (Impact on profit)
- **Priority:** High
- **Steps:** 
  1) اشترِ المنتج بسعر تكلفة 10
  2) بِعه
  3) عدّل التكلفة إلى 12
  4) راجع تقرير الربح
- **Expected:** الربح التاريخي لا يجب أن يتغير بشكل غير مبرر (حسب سياسة التكلفة: AVG/FIFO…).

### TC-PROD-016 — تسعير مختلف حسب المخزن (product_warehouse.price)
- **Priority:** High
- **Preconditions:** تفعيل/استخدام خيار “Different price by warehouse” إن وجد في شاشة المنتج
- **Steps:**
  1) على المنتج `P1` اضبط سعر WH1 = 15.00 وسعر WH2 = 16.50
  2) نفّذ بيع من WH1 ثم بيع من WH2 لنفس المنتج
- **Expected:**
  - سعر السطر في WH1 يأخذ 15.00 وفي WH2 يأخذ 16.50
  - لا يحدث خلط تسعير بين المخازن
  - عند عدم وجود سعر مخزن (NULL) يجب الرجوع إلى `products.price` بشكل واضح وثابت

---

## B) Purchases — المشتريات

### TC-PUR-001 — دورة شراء “Ordered → Partial → Received”
- **Priority:** Critical
- **Steps:**
  1) Purchases → Create: status=Ordered (أو Pending)
  2) أدخل Qty=100، Recieved=0
  3) Save
  4) Edit: اجعل Recieved=40 وstatus=Partial
  5) Edit: اجعل Recieved=100 وstatus=Received
- **Expected:**
  - المخزون يزيد فقط بقدر الـ Recieved (وليس Qty) في كل مرحلة
  - `product_warehouse.qty` و`products.qty` تتطابق مع الاستلام
  - لا يحدث “تكرار إضافة” عند التعديل

### TC-PUR-002 — شراء مع Unit conversion (BOX10)
- **Priority:** High
- **Steps:** اشترِ `P1` بوحدة `BOX10`، qty=2, received=2
- **Expected:** المخزون يزيد بمقدار 20 PCS.

### TC-PUR-003 — شراء Variant بوحدة صحيحة وربط item_code
- **Priority:** High
- **Steps:** شراء `P-VAR-M` بكود المتغير
- **Expected:** يزيد رصيد variant الصحيح فقط + يظهر في `product_warehouse.variant_id`.

### TC-PUR-004 — Purchase tax/discount/shipping correctness
- **Priority:** Critical
- **Steps:** أنشئ Purchase مع:
  - Discount line + Order discount + Order tax + Shipping cost
- **Expected:** 
  - subtotal/total_tax/grand_total تطابق المعادلات
  - لا يتم احتساب الضريبة مرتين

### TC-PUR-005 — منع Qty/Received = 0 (أو التعامل معها)
- **Priority:** High
- **Steps:** أضف سطر بكمية 0 أو received 0 مع status=Received
- **Expected:** 
  - إما منع الحفظ أو تجاهل السطر بشكل واضح
  - عدم إنشاء Purchase تفاصيل صفرية تؤثر على التقارير

### TC-PUR-006 — منع أرقام سالبة (Qty/Cost/Tax)
- **Priority:** Critical
- **Steps:** حاول إدخال qty=-1 أو net_unit_cost=-5 أو tax=-1
- **Expected:** رفض مع validation واضح.

### TC-PUR-007 — Duplicate submit في إنشاء Purchase
- **Priority:** High
- **Steps:** اضغط Save مرتين بسرعة / Refresh بعد الحفظ
- **Expected:** عدم إنشاء فواتير مكررة بنفس المرجع أو بنفس البيانات.

### TC-PUR-008 — Delete Purchase يعكس المخزون والمدفوعات
- **Priority:** Critical
- **Steps:** أنشئ Purchase مستلم + أضف Payment ثم Delete
- **Expected:** 
  - المخزون يعود كما كان
  - Payments تُحذف/تُعكس
  - التقارير تتحدث فورًا

### TC-PUR-009 — تعديل Purchase بعد وجود مبيعات لاحقة (Integrity)
- **Priority:** Critical
- **Steps:**
  1) Purchase Received يضيف مخزون
  2) بيع جزء من المخزون
  3) عدّل الـ Purchase لتقليل received لما دون المبيع
- **Expected:** النظام يمنع التعديل أو يعالجها بطريقة محاسبية صحيحة؛ لا يسمح بأن يصبح المخزون سالب/غير منطقي بدون تنبيه.

### TC-PUR-010 — إضافة مدفوعات متعددة (Split payment على مستوى Purchase)
- **Priority:** Medium
- **Steps:** أضف دفعتين بوسيلتين مختلفتين
- **Expected:** paid_amount يتجمع صحيحًا + payment_status يتحول Paid عند التساوي.

### TC-PUR-011 — Update Payment (فرق المبلغ)
- **Priority:** High
- **Steps:** عدّل دفعة من 100 إلى 80
- **Expected:** paid_amount وpayment_status يتحدثان بدقة؛ لا يترك النظام “Due” خاطئة.

### TC-PUR-012 — ملفات مرفقات Purchase: امتداد غير مسموح
- **Priority:** Medium
- **Steps:** ارفع ملف `.exe` أو امتداد غير مسموح
- **Expected:** رفض الرفع.

### TC-PUR-013 — اسم ملف مرفق متكرر (Overwrite risk)
- **Priority:** High
- **Steps:** ارفع ملفين بنفس الاسم في عمليتين مختلفتين
- **Expected:** لا يتم استبدال الملف القديم؛ يجب إعادة تسمية/توليد اسم فريد.

### TC-PUR-014 — Purchase Return (مرتجع مشتريات) يعكس المخزون
- **Priority:** Critical
- **Steps:** أنشئ مرتجع مشتريات لكمية جزئية ثم راقب المخزون
- **Expected:** المخزون ينقص بنفس الكمية/الوحدة/variant.

### TC-PUR-015 — Purchase Return بعد اعتماد/مدفوعات
- **Priority:** High
- **Steps:** Purchase مدفوعة بالكامل ثم Purchase Return
- **Expected:** معالجة أثر المال (Credit note/Refund) حسب النظام؛ على الأقل ألا تُكسر التقارير/المخزون.

---

## C) Inventory / Warehouses — المخزون والتحويلات والتسويات

### TC-INV-001 — Transfer Completed بين مخزنين
- **Priority:** Critical
- **Steps:** Transfer status=Completed (1): من WH1 إلى WH2 كمية 5 من `P1`
- **Expected:** 
  - WH1 ينقص 5، WH2 يزيد 5
  - إجمالي `products.qty` لا يتغير

### TC-INV-002 — Transfer Draft/Pending لا يغير المخزون
- **Priority:** High
- **Steps:** أنشئ Transfer status=Draft/Not completed
- **Expected:** لا تغيير في `product_warehouse.qty`.

### TC-INV-003 — Transfer بكمية أكبر من الرصيد
- **Priority:** Critical
- **Steps:** حاول تحويل 999 من منتج رصيده 10
- **Expected:** منع العملية (أو على الأقل منع إكمالها)؛ أي رصيد سالب = Defect Critical.

### TC-INV-004 — Transfer مع Unit operator = “/” (كشف خطأ التحويل العكسي)
- **Priority:** Critical
- **Steps:**
  1) أنشئ وحدة مثل `BOX10` حيث التحويل يعتمد على `/` (أو أي وحدة operator='/')
  2) نفّذ Transfer ثم حاول Delete/Cancel
- **Expected:** إلغاء التحويل يعكس الكميات بدقة. أي خلل في العكس = Defect Critical.

### TC-INV-005 — Transfer Variant
- **Priority:** High
- **Steps:** حوّل `P-VAR-M` من WH1 إلى WH2
- **Expected:** الحركة تتم على variant_id الصحيح دون خلط.

### TC-INV-006 — Adjustment (+) إضافة مخزون بسبب زيادة
- **Priority:** High
- **Steps:** Qty Adjustment: +10 لـ P2 في WH1
- **Expected:** `products.qty` و`product_warehouse.qty` يزيدان 10.

### TC-INV-007 — Adjustment (-) صرف/تالف
- **Priority:** Critical
- **Steps:** Qty Adjustment: -999 أكبر من الرصيد
- **Expected:** منع أو تحذير قوي + عدم السماح بسالب.

### TC-INV-008 — Delete Adjustment يعكس أثره بالكامل
- **Priority:** High
- **Steps:** أنشئ adjustment ثم احذفه
- **Expected:** المخزون يعود كما كان.

### TC-INV-009 — Stock Count: إنشاء ملف Initial CSV
- **Priority:** High
- **Steps:** Stock Count → اختر WH1 + Category/Brand filters → Create
- **Expected:** يتم توليد CSV يحتوي Expected qty ويُحفظ reference.

### TC-INV-010 — Stock Count: Finalize بملف غير CSV
- **Priority:** Medium
- **Steps:** ارفع ملف Excel بدل CSV
- **Expected:** رفض واضح.

### TC-INV-011 — Stock Count: فروقات + تحويلها لتسوية (Qty Adjustment)
- **Priority:** Critical
- **Steps:**
  1) ارفع Final CSV مع فروقات (Counted أعلى/أقل)
  2) افتح شاشة Stock difference
  3) نفّذ Qty Adjustment من شاشة الجرد
- **Expected:** المخزون يتعدل بدقة + يتم وسم الجرد `is_adjusted=true`.

### TC-INV-012 — Reorder alert report (Alert qty > qty)
- **Priority:** High
- **Steps:** اجعل P2 qty أقل من alert_quantity ثم افتح التقرير
- **Expected:** المنتج يظهر في تقرير التنبيه.

### TC-INV-013 — Negative stock policy (قفل/سماح)
- **Priority:** Critical
- **Steps:** نفّذ بيع/تحويل/تسوية تسبب سالب
- **Expected:** قرار واحد واضح:
  - **ممنوع** (مفضل) أو
  - **مسموح** مع Audit trail وتقرير خاص + تحذير UI

### TC-INV-014 — Consistency: product.qty == SUM(product_warehouse.qty) للمنتجات القياسية
- **Priority:** Critical
- **Steps:** بعد عدة عمليات (Purchase/Sale/Transfer/Return/Adjustment)
- **Expected:** التطابق (لمنتجات standard غير الرقمية). أي فرق = Defect Critical (فساد مخزون).

### TC-INV-015 — حذف Warehouse عليه حركات
- **Priority:** Critical
- **Steps:** حاول حذف مخزن مرتبط بحركات
- **Expected:** منع الحذف أو تعطيل فقط؛ لا يسمح بكسر العلاقات التاريخية.

---

## D) Sales — المبيعات

### TC-SALE-001 — Sale Completed يخصم المخزون ويثبت الإجماليات
- **Priority:** Critical
- **Steps:** أنشئ Sale status=Completed لمنتج P1 qty=3 مع ضريبة
- **Expected:** 
  - `products.qty` و`product_warehouse.qty` ينقصان (مع مراعاة الوحدة)
  - subtotal/tax/discount/grand_total صحيحة
  - payment_status يتحدد بدقة (Paid إذا paid_amount == grand_total)

### TC-SALE-002 — Sale Draft/ Pending لا يخصم المخزون
- **Priority:** Critical
- **Steps:** أنشئ Sale status=Draft ثم راقب المخزون
- **Expected:** لا تغيير بالمخزون.

### TC-SALE-003 — تحويل Draft إلى Completed
- **Priority:** High
- **Steps:** Edit draft → change status to Completed
- **Expected:** خصم مخزون مرة واحدة فقط (لا تكرار).

### TC-SALE-004 — بيع بكمية أكبر من المتاح
- **Priority:** Critical
- **Steps:** بيع 999 من P2 (المتاح 10)
- **Expected:** يجب أن يُمنع. إذا تم وأصبح المخزون سالب → Defect Critical (خسارة مالية/تشغيلية).

### TC-SALE-005 — بيع منتج Inactive
- **Priority:** Critical
- **Steps:** حاول بيع P-INACT
- **Expected:** منع إضافة المنتج/إتمام البيع.

### TC-SALE-006 — Sale مع Unit conversion (BOX10)
- **Priority:** High
- **Steps:** بيع P1 بوحدة BOX10 qty=2
- **Expected:** خصم 20 PCS من المخزون.

### TC-SALE-007 — Sale Variant (item_code)
- **Priority:** High
- **Steps:** بيع `P-VAR-L`
- **Expected:** خصم رصيد variant فقط وبشكل صحيح.

### TC-SALE-008 — Sale Combo يخصم مكونات الكومبو
- **Priority:** Critical
- **Steps:** بيع `P-COMBO` qty=1
- **Expected:** خصم P1 x2 وP2 x1 (وليس P-COMBO كرصيد).

### TC-SALE-009 — Coupon تطبيقه وحدوده
- **Priority:** High
- **Steps:** بيع مع coupon ثم راقب الخصم وقيمة used
- **Expected:** خصم صحيح + زيادة used مرة واحدة فقط؛ عند حذف الفاتورة ينقص used.

### TC-SALE-010 — Discount > line total (منع)
- **Priority:** Critical
- **Steps:** ضع خصم أكبر من قيمة السطر
- **Expected:** منع أو تصحيح تلقائي؛ لا يسمح بإجمالي سالب.

### TC-SALE-011 — Tax rounding على أكثر من سطر
- **Priority:** High
- **Steps:** سطور متعددة بقيم كسور + VAT 15%
- **Expected:** نفس نتيجة الضريبة في الفاتورة والتقرير، بلا فروقات rounding غير مفسرة.

### TC-SALE-012 — Payments: Partial ثم إضافة دفعة لاحقة
- **Priority:** High
- **Steps:** إنشاء Sale paid_amount < grand_total ثم Add Payment
- **Expected:** paid_amount يتحدث + payment_status يتحول Partial/Due/Paid بشكل صحيح.

### TC-SALE-013 — Update Payment (تعديل مبلغ)
- **Priority:** High
- **Steps:** تعديل دفعة ثم تحقق من due
- **Expected:** due صحيح وعدم كسر cash register totals.

### TC-SALE-014 — Delete Payment (حذف دفعة)
- **Priority:** High
- **Steps:** حذف دفعة مرتبطة بفاتورة
- **Expected:** paid_amount ينقص ويُعاد حساب payment_status.

### TC-SALE-015 — Delete Sale يعكس المخزون والمدفوعات والتقارير
- **Priority:** Critical
- **Steps:** أنشئ Sale Completed + Payments + Delivery ثم Delete
- **Expected:** رجوع المخزون + حذف delivery + حذف payments + تحديث التقارير.

### TC-SALE-016 — Delivery/Shipment: إضافة وتعديل وحذف
- **Priority:** Medium
- **Steps:** Add delivery ثم update ثم delete
- **Expected:** لا يؤثر على المخزون (إذا المخزون خُصم عند sale) إلا إذا النظام يعتبره “صرف”.

### TC-SALE-017 — Sales Return جزئي يعيد المخزون
- **Priority:** Critical
- **Steps:** Return-sale qty أقل من المبيع
- **Expected:** المخزون يزيد + إجماليات المرتجع (tax/discount) تتعكس بشكل صحيح.

### TC-SALE-018 — Sales Return كامل + Refund (Cash/Deposit)
- **Priority:** High
- **Steps:** مرتجع كامل لعملية مدفوعة نقدًا ثم تحقق من cash register totals
- **Expected:** يظهر في `returns` ويؤثر على “total sale return”.

### TC-SALE-019 — تاريخ مرتجع قبل تاريخ البيع
- **Priority:** High
- **Steps:** حاول إدخال تاريخ مرتجع أقدم من البيع (إن كانت هناك حقول تاريخ)
- **Expected:** منع أو تحذير/تقييد واضح.

### TC-SALE-020 — فتح نفس الفاتورة في Tabين وتعديل متزامن
- **Priority:** High
- **Steps:** افتح Edit في نافذتين، عدّل في الأولى ثم احفظ الثانية
- **Expected:** منع تضارب (Optimistic lock) أو على الأقل تحذير؛ لا يسمح بكسر المخزون/المدفوعات.

### TC-SALE-021 — سعر المخزن NULL: fallback للسعر الأساسي
- **Priority:** Medium
- **Steps:**
  1) اجعل `product_warehouse.price` فارغًا لمنتج في مخزن معيّن (عبر واجهة المنتج إن أمكن)
  2) نفّذ بيع من نفس المخزن
- **Expected:** النظام يستخدم `products.price` ولا يظهر السعر 0 أو Null في الفاتورة/التقارير.

---

## E) POS — نقاط البيع

### TC-POS-001 — فتح Cash Register وربطه بالمبيعات
- **Priority:** Critical
- **Steps:** افتح Cash Register لـ WH1 بمبلغ Opening ثم نفّذ POS Sale
- **Expected:** sale.cash_register_id يتعبأ + payments.cash_register_id يتعبأ.

### TC-POS-002 — منع فتح أكثر من Register لنفس المستخدم/المخزن
- **Priority:** High
- **Steps:** حاول فتح register ثاني لنفس user+warehouse
- **Expected:** يجب منع (أو يظهر تحذير قوي). تعدد الصناديق لنفس الوردية = خطر مالي.

### TC-POS-003 — Barcode scan (limsProductSearch) يعيد المنتج الصحيح
- **Priority:** Critical
- **Steps:** امسح `P-STD-TAX` ثم `P-VAR-M`
- **Expected:** يضيف السطر الصحيح بالسعر الصحيح (Promotion/Variant additional price/Tax).

### TC-POS-004 — Duplicate scan يزيد الكمية ولا يكرر السطر (UX)
- **Priority:** Medium
- **Steps:** امسح نفس الباركود 3 مرات
- **Expected:** إما زيادة qty في نفس السطر أو سلوك واضح ثابت (حسب تصميم النظام).

### TC-POS-005 — Hold order (Draft) ثم Resume ثم Complete
- **Priority:** High
- **Steps:** أنشئ Draft من POS ثم استرجعه من قائمة drafts وأكمله
- **Expected:** لا تأثير على المخزون أثناء Draft، ثم خصم مرة واحدة عند الإكمال.

### TC-POS-006 — Split Payment عبر دفعات متعددة
- **Priority:** High
- **Steps:** ادفع جزء Cash ثم أضف دفعة Card
- **Expected:** paid_amount يساوي grand_total في النهاية + cash register breakdown صحيح.

### TC-POS-007 — Change (الباقي للعميل) صحيح
- **Priority:** Medium
- **Steps:** Paying amount أكبر من المطلوب
- **Expected:** يتم تسجيل change صحيح ولا يرفع paid_amount فوق المطلوب.

### TC-POS-008 — Refund/Return من POS يؤثر على الصندوق
- **Priority:** Critical
- **Steps:** أنشئ بيع ثم نفّذ return مرتبط بنفس الوردية
- **Expected:** `total_sale_return` يزيد في تفاصيل الصندوق.

### TC-POS-009 — Void/Cancel invoice (إن كان عبر Delete)
- **Priority:** Critical
- **Steps:** حاول حذف فاتورة POS مكتملة
- **Expected:** يجب تقييدها (صلاحية + سبب + Audit). حذف بلا أثر = خطر.

### TC-POS-010 — Session timeout أثناء POS
- **Priority:** High
- **Steps:** اترك POS حتى انتهاء الجلسة ثم حاول الحفظ
- **Expected:** لا فقدان صامت للطلب؛ يجب حفظ Draft أو تحذير واضح.

### TC-POS-011 — Offline/Slow response (عملي)
- **Priority:** Medium
- **Steps:** خفّض الشبكة/أغلقها أثناء Save
- **Expected:** منع double submit + رسالة “لم يتم الحفظ” واضحة.

### TC-POS-012 — إغلاق الوردية (Close register) وحساب Total cash
- **Priority:** Critical
- **Steps:** أغلق الصندوق بعد مبيعات + مرتجعات + مصروفات
- **Expected:** Total cash = opening + payments - (returns + expenses) ويطابق التقرير.

---

## F) Reports — التقارير

### TC-REP-001 — Sales Report يطابق فواتير sales
- **Priority:** Critical
- **Steps:** نفّذ 3 فواتير بأيام مختلفة ثم استخرج تقرير المبيعات لنفس الفترة
- **Expected:** إجمالي grand_total والضرائب والخصومات متطابق مع جدول `sales`.

### TC-REP-002 — Purchase Report يطابق purchases
- **Priority:** High
- **Steps:** نفّذ مشتريات + مرتجع مشتريات ثم تقرير مشتريات
- **Expected:** المشتريات والمرتجعات تظهر بوضوح وبأثر صحيح.

### TC-REP-003 — Warehouse stock report يطابق product_warehouse
- **Priority:** Critical
- **Steps:** بعد عمليات Transfer/Sale/Purchase
- **Expected:** إجمالي الكميات والقيم يطابق المخزون الفعلي لكل مخزن.

### TC-REP-004 — Profit/Loss منطقي على Dataset معروف
- **Priority:** High
- **Steps:** اعمل Purchase cost=10 ثم Sale price=15 لكمية 10 ثم راجع التقرير
- **Expected:** ربح إجمالي قريب من (15-10)*10 مع تأثير الضرائب/الخصومات حسب التعريف.

### TC-REP-005 — Best seller يتأثر بالمبيعات والمرتجعات
- **Priority:** Medium
- **Steps:** بيع P1 ثم مرتجع جزئي
- **Expected:** الكمية الصافية في best seller لا تحتسب المرتجع كمبيعات.

### TC-REP-006 — Tax consistency: تقرير الضرائب vs الفواتير
- **Priority:** Critical
- **Steps:** فواتير ضريبية/غير ضريبية + خصم قبل/بعد الضريبة
- **Expected:** نفس الضريبة المحسوبة في الفاتورة تظهر في تقرير الضرائب.

### TC-REP-007 — POS/Cash register details تطابق payments/returns/expenses
- **Priority:** Critical
- **Steps:** افتح cash register → مبيعات → مرتجع → مصروف → تفاصيل الصندوق
- **Expected:** جميع الأرقام متطابقة ولا يوجد “تسريب” مدفوعات خارج الصندوق.

---

## G) Permissions & Security — الصلاحيات والأمان

### TC-SEC-001 — Cashier لا يرى Cost ولا تقارير الربح
- **Priority:** Critical
- **Steps:** سجل دخول كـ cashier وافتح:
  - صفحة المنتج/التقارير/Profit loss
- **Expected:** منع الوصول + عدم ظهور cost في UI/API.

### TC-SEC-002 — Warehouse user لا ينشئ Sales/Purchases
- **Priority:** High
- **Steps:** دخول wh1 ثم محاولة فتح `/sales/create` و`/purchases/create`
- **Expected:** منع/Redirect مع رسالة صلاحيات.

### TC-SEC-003 — Direct URL access (IDOR) على مستندات ليست للمستخدم
- **Priority:** Critical
- **Preconditions:** تفعيل staff_access = own لموظفين
- **Steps:** مستخدم sales1 يحاول فتح invoice لبيع أنشأه admin1 عبر URL
- **Expected:** منع أو عرض مقيد حسب السياسة؛ أي كشف بيانات عميل/أسعار = Defect High/Critical.

### TC-SEC-004 — Tampering: تعديل سعر/خصم عبر DevTools قبل الإرسال
- **Priority:** Critical
- **Steps:** في POS غيّر قيم hidden inputs (net_unit_price/discount/tax) ثم أرسل
- **Expected:** يجب إعادة الحساب/التحقق في السيرفر؛ رفض أي قيم غير متناسقة.

### TC-SEC-005 — CSRF على عمليات POST الحساسة
- **Priority:** High
- **Steps:** نفّذ POST بدون CSRF token (باستخدام Postman) لمسارات إنشاء بيع/شراء
- **Expected:** رفض 419/CSRF mismatch (Laravel).

### TC-SEC-006 — XSS في اسم المنتج/ملاحظات الفاتورة
- **Priority:** High
- **Steps:** أدخل `<script>alert(1)</script>` في product name أو notes ثم اعرضها
- **Expected:** عرض نصي مُعقّم بدون تنفيذ.

### TC-SEC-007 — File upload path traversal / double extension
- **Priority:** High
- **Steps:** ارفع ملف باسم `a.jpg.php` أو اسم يحتوي `../`
- **Expected:** رفض أو تخزين آمن مع اسم مولّد + منع التنفيذ.

### TC-SEC-008 — Auditability: من أنشأ/عدّل/حذف
- **Priority:** High
- **Steps:** نفّذ إنشاء/تعديل/حذف على بيع/تحويل/تسوية
- **Expected:** وجود `created_by/updated_by` أو log واضح. إن لم يوجد: اعتبرها Gap عالية المخاطر.
