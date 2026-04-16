# UAT Cases — سيناريوهات قبول المستخدم (UAT)

> هذه السيناريوهات تُنفّذ بواسطة مستخدمين فعليين (مدير/محاسب/كاشير/مخزن/مشتريات/مبيعات) على Staging قبل Go‑Live.
>
> بيانات موحدة للاختبار: `docs/QA/00_Test_Data_Setup.md`.

---

## تعليمات UAT (مختصر عملي)

- لكل UAT Case:
  - اجمع **Evidence**: رقم المرجع (Reference No)، لقطة شاشة للفاتورة/الحركة، وملخص تقرير مرتبط.
  - أي اختلاف بين (فاتورة ↔ مخزون ↔ تقرير) = **Fail**.
- لا تُعدّل البيانات يدويًا في DB أثناء UAT.
- عيّن مسؤول قرار (Business Owner) لكل Case لتوقيع Pass/Fail.

---

## قائمة UAT Cases (قابلة للتوقيع)

### UAT-01 — تهيئة Master Data الأساسية (Manager)
- **Role:** Manager
- **Steps:**
  1) إنشاء ضريبة VAT 15% وNo Tax
  2) إنشاء وحدات PCS وBOX10
  3) إنشاء مخزنين WH1/WH2
  4) إنشاء Biller واحد على الأقل
- **Acceptance Criteria:**
  - تظهر البيانات في القوائم المنسدلة بكل الشاشات (Products/Sales/Purchases/POS)
  - لا توجد قيم مكررة/غير قابلة للاختيار

### UAT-02 — إنشاء منتجات واقعية (Manager)
- **Role:** Manager
- **Steps:** إنشاء P1/P2/P3(Variant)/P4(Combo)/P5(Digital)/P6(Inactive)/P7(Promo)
- **Acceptance Criteria:**
  - البحث بالكود يعمل
  - المنتج Inactive لا يظهر في POS
  - Promo يظهر بسعره ضمن المدة

### UAT-03 — دورة شراء + استلام جزئي ثم كامل (Purchasing)
- **Role:** Purchasing Officer
- **Steps:**
  1) إنشاء Purchase status=Ordered مع Qty=100 Received=0
  2) تعديلها إلى Partial مع Received=40
  3) تعديلها إلى Received مع Received=100
- **Acceptance Criteria:**
  - المخزون يزيد تدريجيًا حسب Received فقط
  - لا يوجد تكرار في المخزون بعد التعديل

### UAT-04 — شراء Variant + تحقق رصيد المتغير (Purchasing + Warehouse)
- **Role:** Purchasing Officer + Warehouse User
- **Steps:** شراء `P-VAR-M` ثم فتح شاشة مخزون المخزن
- **Acceptance Criteria:** الرصيد للـ variant الصحيح فقط.

### UAT-05 — شراء مع شحن/خصم/ضريبة (Purchasing + Accountant)
- **Role:** Purchasing Officer + Accountant
- **Steps:** Purchase مع Order discount + Order tax + Shipping + دفع جزئي
- **Acceptance Criteria:**
  - grand_total صحيح
  - due صحيح
  - payment_status يتغير حسب المدفوع

### UAT-06 — مرتجع مشتريات جزئي (Purchasing + Warehouse)
- **Role:** Purchasing Officer + Warehouse User
- **Steps:** Purchase Return لكمية 10 من منتج مستلم
- **Acceptance Criteria:** المخزون ينقص 10 + يظهر المرتجع في التقارير.

### UAT-07 — بيع نقدي مكتمل من المخزون (Cashier)
- **Role:** Cashier
- **Steps:** POS sale لـ P1 qty=3 نقدًا مع VAT
- **Acceptance Criteria:**
  - المخزون ينقص
  - الفاتورة مطبوعة (أو PDF) صحيحة الإجماليات
  - يظهر في تقرير اليوم

### UAT-08 — Hold/Resume order (Cashier)
- **Role:** Cashier
- **Steps:** إنشاء Draft في POS → Resume → Complete
- **Acceptance Criteria:**
  - لا خصم مخزون أثناء Draft
  - خصم مرة واحدة عند الإكمال

### UAT-09 — Split payment (Cash + Card) (Cashier + Accountant)
- **Role:** Cashier + Accountant
- **Steps:** دفع جزء Cash ثم إضافة دفعة Card لنفس الفاتورة
- **Acceptance Criteria:**
  - paid_amount = grand_total في النهاية
  - تفصيل الدفعات يظهر صحيحًا
  - تقرير الصندوق يعرض Cash/Card بشكل صحيح

### UAT-10 — بيع آجل (Due) ثم تحصيل لاحق (Sales + Accountant)
- **Role:** Sales User + Accountant
- **Steps:** إنشاء Sale paid=0 ثم إضافة دفعة لاحقة
- **Acceptance Criteria:** due ينخفض + payment_status يتحول (Due → Paid).

### UAT-11 — بيع Combo (Cashier)
- **Role:** Cashier
- **Steps:** بيع P-COMBO qty=1
- **Acceptance Criteria:** خصم مكونات الكومبو (P1/P2) بمقادير صحيحة.

### UAT-12 — بيع Digital (Cashier)
- **Role:** Cashier
- **Steps:** بيع P-DIGI
- **Acceptance Criteria:** لا تأثير على المخزون.

### UAT-13 — مرتجع مبيعات جزئي + Refund (Cashier)
- **Role:** Cashier
- **Steps:** Return-sale لكمية 1 من P1 من فاتورة سابقة
- **Acceptance Criteria:**
  - المخزون يزيد
  - قيمة المرتجع تظهر في تقرير الصندوق (Sale return)

### UAT-14 — حذف/إلغاء فاتورة POS مكتملة (Manager)
- **Role:** Manager
- **Steps:** محاولة حذف فاتورة مكتملة
- **Acceptance Criteria:**
  - لا يسمح بدون صلاحية/سبب واضح
  - إذا سُمح: يعكس المخزون والمدفوعات ويترك أثر Audit

### UAT-15 — تحويل مخزني WH1 → WH2 (Warehouse)
- **Role:** Warehouse User
- **Steps:** Transfer Completed كمية 5 من P1
- **Acceptance Criteria:** WH1 ينقص وWH2 يزيد؛ إجمالي المنتج لا يتغير.

### UAT-16 — Transfer بكمية أكبر من الرصيد (Warehouse)
- **Role:** Warehouse User
- **Steps:** محاولة تحويل كمية أكبر من المتاح
- **Acceptance Criteria:** منع العملية (لا يسمح بسالب).

### UAT-17 — تسعير مختلف حسب المخزن (Manager + Cashier)
- **Role:** Manager + Cashier
- **Steps:** ضبط سعر WH1 ≠ WH2 ثم بيع من كل مخزن
- **Acceptance Criteria:** السعر يتبع المخزن الصحيح في الفاتورة.

### UAT-18 — تسوية مخزون (+/-) بسبب تلف/زيادة (Warehouse + Manager)
- **Role:** Warehouse User + Manager
- **Steps:** Adjustment -2 بسبب تلف ثم +3 بسبب زيادة
- **Acceptance Criteria:** المخزون يتغير بدقة، وتوجد ملاحظات/مستند داعم إن كانت مطلوبة.

### UAT-19 — جرد مخزون عبر CSV ثم اعتماد التسوية (Warehouse)
- **Role:** Warehouse User
- **Steps:** Stock count → Finalize CSV → Qty adjustment
- **Acceptance Criteria:** يتم تحديث المخزون ويُعلّم الجرد كـ Adjusted.

### UAT-20 — تقرير Warehouse stock (Manager)
- **Role:** Manager
- **Steps:** فتح تقرير مخزون مخزن WH1 ثم WH2
- **Acceptance Criteria:** الرصيد يطابق شاشة الأصناف بالمخزن.

### UAT-21 — تقرير Profit/Loss على فترة محددة (Manager + Accountant)
- **Role:** Manager + Accountant
- **Steps:** تشغيل التقرير لفترة تشمل شراء + بيع + مرتجع
- **Acceptance Criteria:** الربح منطقي ولا يتجاهل المرتجعات.

### UAT-22 — تقرير الضرائب (Accountant)
- **Role:** Accountant
- **Steps:** استخراج تقرير ضرائب لنفس فترة فواتير VAT
- **Acceptance Criteria:** إجمالي VAT يطابق مجموع VAT في الفواتير.

### UAT-23 — تقرير الصندوق/الكاشير (Manager)
- **Role:** Manager
- **Steps:** فتح تفاصيل Cash register بعد إغلاق الوردية
- **Acceptance Criteria:** Total cash = opening + payments - (returns + expenses) ويطابق الواقع.

### UAT-24 — صلاحيات الوصول الأساسية (All roles)
- **Role:** كل الأدوار
- **Steps:** كل مستخدم يحاول الدخول لشاشات خارج صلاحياته (Products cost / Profit report / Stock adjustment…)
- **Acceptance Criteria:** منع وصول واضح + عدم وجود التفاف عبر URL.

### UAT-25 — قابلية التشغيل اليومي (End‑of‑Day)
- **Role:** Manager + Cashier + Accountant
- **Steps:** يوم كامل: فتح وردية → مبيعات/مرتجعات → مصروف → إغلاق وردية → استخراج تقارير اليوم
- **Acceptance Criteria:** لا فروقات بين (الصندوق ↔ تقرير POS ↔ تقرير المبيعات ↔ المرتجعات).

