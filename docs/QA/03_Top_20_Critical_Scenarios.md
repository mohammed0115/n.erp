# Top 20 Critical Scenarios (Must‑Pass قبل Go‑Live)

هذه قائمة “Stop‑the‑line” — أي فشل فيها يعني **مخاطر مالية/تشغيلية عالية** ويمنع الإطلاق حتى الإصلاح أو وجود ضبط بديل معتمد.

> استخدم Dataset من `docs/QA/00_Test_Data_Setup.md` وخذ Evidence (Invoice/Ref No + لقطات + تقرير مرتبط + تحقق مخزون).

---

## 1) بيع كمية أكبر من المتاح (Negative Stock)
- **Why Critical:** خسارة مالية + انهيار ثقة المخزون.
- **Must Check:** يمنع الإكمال أو يمنع السالب/يطلب صلاحية + سبب + Audit.

## 2) Sale Completed لا يخصم المخزون
- **Why Critical:** الإيراد يُسجّل بدون خروج بضاعة.
- **Must Check:** `products.qty` و`product_warehouse.qty` تنقص مع كل سطر مخزني.

## 3) تعديل Sale Completed (زيادة/تغيير أصناف) يسبب خصم/إرجاع غير صحيح
- **Why Critical:** فساد مخزون وصعوبة تتبعه.
- **Must Check:** التعديل يعكس القديم ثم يطبّق الجديد بدون مضاعفة.

## 4) حذف Sale Completed لا يعكس المخزون والمدفوعات
- **Why Critical:** تضخيم مخزون/إيراد/صندوق أو فقدان أثر مالي.
- **Must Check:** reversal كامل + منع حذف بدون صلاحية/سبب.

## 5) Draft/Hold في POS يتحول لمكتمل ويخصم مرتين (double apply)
- **Why Critical:** عجز مخزون مفاجئ.
- **Must Check:** خصم مرة واحدة فقط عند الإكمال.

## 6) Split payments / دفعات متعددة تُضاعف paid_amount أو تكسر due
- **Why Critical:** أخطاء تحصيل/ذمم/صندوق.
- **Must Check:** مجموع الدفعات = paid_amount، وpayment_status يتحول بدقة.

## 7) Cash Register: فتح أكثر من وردية لنفس المستخدم/المخزن
- **Why Critical:** تفاوت الصندوق وعدم قدرة على إقفال صحيح.
- **Must Check:** منع أو سياسة واضحة + تقرير يميّز الوردية.

## 8) Cash Register totals لا تساوي (Opening + Payments - Returns - Expenses)
- **Why Critical:** فروقات مالية مباشرة.
- **Must Check:** تفاصيل الصندوق تطابق الواقع وتفصيل طرق الدفع صحيح.

## 9) Purchase Partial/Received يضيف المخزون بشكل خاطئ (Qty بدل Received)
- **Why Critical:** تضخم مخزون.
- **Must Check:** فقط Received يدخل المخزون، والتعديل لا يكرر الإضافة.

## 10) تعديل Purchase بعد حدوث مبيعات لاحقة (Backdating) يسبب سالب/فساد
- **Why Critical:** كسر تاريخ المخزون ونتائج الربح.
- **Must Check:** منع/قيد واضح أو معالجة محاسبية صحيحة.

## 11) حذف Purchase مستلم لا يعكس المخزون
- **Why Critical:** رصيد وهمي.
- **Must Check:** reversal كامل + منع الحذف بدون صلاحية/سبب.

## 12) Purchase Return لا يخصم المخزون أو يخصم من variant خاطئ
- **Why Critical:** فساد مخزون/تسوية خاطئة.
- **Must Check:** خصم الكمية الصحيحة من نفس المخزن ونفس الـ variant.

## 13) Sales Return لا يعيد المخزون أو لا يعكس الضريبة/الخصم
- **Why Critical:** تقرير ضرائب وربح خاطئ.
- **Must Check:** stock back + VAT/discount reverse + يظهر في التقارير.

## 14) Combo product يخصم مكونات خاطئة أو كميات خاطئة
- **Why Critical:** عجز/زيادة مخزون على أصناف مكونات.
- **Must Check:** خصم حسب BOM (qty_list) بدقة.

## 15) Variant barcode/item_code يبيع/يشتري Variant خاطئ
- **Why Critical:** خلط أصناف/مقاسات + شكاوى عملاء.
- **Must Check:** item_code مرتبط بvariant_id الصحيح في كل الوثائق.

## 16) Transfer Completed لا يوازن بين WH1 وWH2
- **Why Critical:** اختلاف رصيد بين المخازن.
- **Must Check:** WH1-qty وWH2+qty وإجمالي المنتج ثابت.

## 17) Cancel/Delete Transfer لا يعكس (خصوصاً مع وحدات operator='/')
- **Why Critical:** رصيد مخزن غير صحيح بعد الإلغاء.
- **Must Check:** reversal صحيح لكل وحدات التحويل.

## 18) Stock Count CSV: finalize/adjustment يطبق فروقات على أصناف خاطئة
- **Why Critical:** تسويات جرد كارثية.
- **Must Check:** مطابقة code في CSV مع product_id الصحيح + تطبيق action +/- صحيح.

## 19) Reporting inconsistency: تقرير المخزون/المبيعات لا يطابق المستندات
- **Why Critical:** الإدارة ستتخذ قرارات خاطئة.
- **Must Check:** تقارير الفترة تطابق مجموع المستندات الفعلية.

## 20) Security: Tampering/IDOR (تعديل سعر/خصم عبر المتصفح أو الوصول لفواتير غير مسموحة)
- **Why Critical:** احتيال مالي وتسريب بيانات.
- **Must Check:** إعادة حساب في السيرفر + منع الوصول عبر URL + تسجيل Audit.

