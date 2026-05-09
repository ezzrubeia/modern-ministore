// IIFE: نغلف كل الكود داخل دالة تُنفّذ مباشرة حتى ما تتعارض أسماء المتغيرات مع ملفات ثانية
(function(){ 

  // تعريف دالة مختصرة اسمها $ لاختيار أول عنصر مطابق للـ selector داخل سياق معين (افتراضيًا document)
  const $ = (sel, ctx=document) => ctx.querySelector(sel);

  // تعريف دالة مختصرة اسمها $$ لاختيار كل العناصر المطابقة وتحويل NodeList إلى Array عشان نقدر نستخدم map/filter بسهولة
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

  // قراءة متغير عام من window اسمه IS_ADMIN وتحويله لقيمة Boolean أكيدة باستخدام !!
  const IS_ADMIN  = !!window.IS_ADMIN;

  // قراءة متغير عام من window اسمه IS_LOGGED وتحويله لقيمة Boolean أكيدة باستخدام !!
  const IS_LOGGED = !!window.IS_LOGGED;

  // كائن T: قاموس نصوص ثابتة (Translations/Labels) نستخدمه بدل ما نكتب النصوص في كل مكان
  const T = {
    // اسم البراند/العنوان
    brand: "Modern MiniStore 🛍️",
    // نص يظهر للمستخدم إذا لازم يسجل دخول
    loginToBuy: "سجّل الدخول للشراء",
    // نص زر إضافة للعربة
    addToCart: "إضافة للعربة",
    // كلمة "المخزون"
    stock: "المخزون",
    // كلمة "تعديل"
    edit: "تعديل",
    // كلمة "حذف"
    del: "حذف",
    // عنوان نافذة الترحيب
    welcomeTitle: "مرحبًا بك!",
    // نص نافذة الترحيب
    welcomeBody: "تسوق بتجربة بنفسجية أنيقة ✨",
    // نص زر البدء
    start: "ابدأ",
    // رسالة عند الإضافة للعربة
    added: "تمت الإضافة إلى العربة",
    // رسالة عند تنفيذ الطلب
    placed: "تم تنفيذ الطلب",
    // رسالة عند تسجيل الدخول
    logged: "تم تسجيل الدخول",
    // رسالة عامة عند الفشل
    failed: "حدث خطأ",
  };

  // دالة ترجمة t: تدخل مفتاح k وترجع النص من القاموس T، وإذا المفتاح مش موجود ترجع نفس المفتاح
  const t = (k)=> T[k] || k;

  // دالة injectChrome: مسؤولة عن إضافة "الهيدر" و "حاوية التوست" و "مودال الترحيب" إذا مش موجودين
  function injectChrome(){

    // شرط: إذا ما في عنصر على الصفحة class اسمه site-header
    if (!$('.site-header')){

      // إنشاء عنصر HTML جديد من نوع header
      const header = document.createElement('header');

      // إعطاء العنصر className حتى نقدر ننسقه في CSS
      header.className = 'site-header';

      // وضع HTML داخلي للـ header باستخدام template literal (backticks) لسهولة إدخال متغيرات ${}
      header.innerHTML = `
        <div class="container inner">
          <div class="brand">
            <div class="logo">M</div>
            <span>${t('brand')}</span>
          </div>
        </div>`;

      // إضافة الهيدر كأول عنصر داخل body (قبل أي شيء)
      document.body.prepend(header);
    }

    // شرط: إذا ما في عنصر id اسمه toast-stack (حاوية إشعارات التوست)
    if (!$('#toast-stack')){

      // إنشاء div جديد ليكون حاوية للتوستات
      const wrap = document.createElement('div');

      // تعيين id للحاوية عشان نقدر نجيبها بسهولة
      wrap.id = 'toast-stack';

      // إضافة الحاوية لآخر body
      document.body.append(wrap);
    }

    // شرط: إذا localStorage ما فيه مفتاح welcomed → يعني المستخدم أول مرة يدخل
    if (!localStorage.getItem('welcomed')){

      // إنشاء div جديد ليكون المودال
      const modal = document.createElement('div');

      // تعيين id للمودال
      modal.id = 'welcome-modal';

      // وضع HTML داخلي للمودال: كرت + عنوان + نص + زر
      modal.innerHTML = `
        <div class="card">
          <h3>${t('welcomeTitle')}</h3>
          <p class="muted mt-1">${t('welcomeBody')}</p>
          <div class="actions mt-2">
            <button class="btn primary" id="welcome-go">${t('start')}</button>
          </div>
        </div>`;

      // إضافة المودال إلى الصفحة
      document.body.append(modal);

      // requestAnimationFrame: ننتظر فريم رسم واحد ثم نضيف class show عشان أنيميشن CSS يشتغل صح
      requestAnimationFrame(()=> modal.classList.add('show'));

      // إضافة حدث click على زر داخل المودال id=welcome-go
      modal.querySelector('#welcome-go').addEventListener('click', ()=>{

        // عند الضغط: إزالة class show لإخفاء المودال (CSS)
        modal.classList.remove('show');

        // تخزين welcomed=1 في localStorage حتى ما يرجع يطلع مرة ثانية
        localStorage.setItem('welcomed', '1');
      });
    }
  }

  // دالة toast: تعمل إشعار صغير يظهر ثم يختفي بعد مدة
  function toast(msg, ms=2200){

    // جلب عنصر حاوية التوستات من الصفحة
    const host = $('#toast-stack');

    // إذا الحاوية غير موجودة، نوقف ونطلع من الدالة
    if (!host) return;

    // إنشاء div جديد يمثل توست واحد
    const el = document.createElement('div');

    // تعيين class toast عليه (عشان التنسيق والأنيميشن)
    el.className = 'toast';

    // وضع النص داخل التوست كنص عادي (textContent آمن من HTML)
    el.textContent = msg;

    // إضافة التوست داخل الحاوية
    host.append(el);

    // تشغيل أنيميشن الظهور بإضافة class show بعد فريم واحد
    requestAnimationFrame(()=> el.classList.add('show'));

    // بعد ms ميلي ثانية: نخفي التوست ثم نحذفه من DOM
    setTimeout(()=> { 

      // إزالة class show لتفعيل أنيميشن الاختفاء
      el.classList.remove('show');

      // بعد 350ms (مدة الأنيميشن) نحذف العنصر نهائيًا
      setTimeout(()=> el.remove(), 350);

    // مدة بقاء التوست ظاهر
    }, ms);
  }

  // إنشاء IntersectionObserver: يراقب العناصر ويشغل reveal-in لما تدخل الشاشة
  const io = new IntersectionObserver((entries)=>{

    // المرور على كل entry (كل عنصر تم مراقبته)
    entries.forEach(e=>{

      // شرط: إذا العنصر دخل ضمن منطقة الرؤية (صار ظاهر)
      if (e.isIntersecting) {

        // إضافة class reveal-in للعنصر لتفعيل أنيميشن CSS
        e.target.classList.add('reveal-in');

        // إيقاف مراقبة العنصر بعد ما ظهر مرة (حتى ما تتكرر)
        io.unobserve(e.target);
      }
    });

  // threshold: يبدأ التفعيل عندما يظهر 12% من العنصر
  }, { threshold: .12 });

  // دالة watchReveal: تختار عناصر معينة وتخلي observer يراقبها
  function watchReveal(){

    // اختيار كل .card وأي عنصر عنده data-reveal وصفوف الجدول .table tr
    $$('.card, [data-reveal], .table tr')

      // لكل عنصر: نبدأ مراقبته في IntersectionObserver
      .forEach(el=> io.observe(el));
  }

  // تخزين عنصر قائمة المنتجات لو موجود بالصفحة (مثلاً index.php)
  const listEl = $('#products-list');

  // دالة fetchJSON: تجيب JSON من رابط باستخدام fetch وتتأكد من نجاح الرد
  async function fetchJSON(url){

    // إرسال طلب HTTP GET للرابط url
    //استنى لحد ما الطلب يخلص ويرجع ردAWAIT:.
    const r = await fetch(url);

    // إذا الاستجابة ليست OK (مثل 404/500) نرمي خطأ
    // نرمي خطأ → ينتقل مباشرة إلى catch.
    if(!r.ok) throw new Error(r.status);

    // تحويل الاستجابة إلى JSON وإرجاعها
    return r.json();
  }

  // دالة card: تبني HTML لكرت منتج واحد بناءً على بيانات it
  function card(it){

    // متغير admin: إذا المستخدم أدمن نعرض أزرار تعديل/حذف، غير هيك نخليه فاضي
    const admin = IS_ADMIN 
      ? (
          // زر تعديل يحمل data-edit=id
          `<button class="btn" data-edit="${it.id}">${t('edit')}</button>
           // زر حذف يحمل data-del=id
           <button class="btn danger" data-del="${it.id}">${t('del')}</button>`
        ) 
      : '';

   // متغير buy: زر يودّي على صفحة تفاصيل المنتج مع id بالـ query string
   const buy = `
  <a class="btn primary" href="product_details.php?id=${it.id}">
    عرض التفاصيل
  </a>
`;

    // إرجاع HTML كامل للكرت
    return `<article class="card product" data-reveal>

      <img src="${it.image_url || 'https://picsum.photos/seed/p'+it.id+'/400/300'}" alt="${it.name||''}">

      <div class="row" style="justify-content:space-between;margin-top:8px;">

        <h3 style="margin:0">${it.name}</h3>

        <strong>${Number(it.price).toFixed(2)} USD</strong>
      </div>

      
      <p class="muted" style="margin:6px 0">
        ${t('stock')}: ${it.stock}
      </p>

      <div class="row" style="gap:8px;flex-wrap:wrap">
        ${admin}${buy}
      </div>
    </article>`;
  }

  // دالة load: تحمل المنتجات من API وترسمها بالصفحة
  async function load(){

    // إذا الصفحة ما فيها #products-list نوقف
    if (!listEl) return;

    // try/catch لمعالجة أخطاء الشبكة أو JSON
    try {

      // جلب المنتجات من api/products.php
      const items = await fetchJSON('api/products.php');

      // تخزين آخر منتجات في window (للاستخدام من الكونسول أو مستقبلًا)
      window.__lastProducts = items;

      // تعريف دالة عالمية لإعادة الرسم لأي مصفوفة منتجات
      window.__renderProducts = (arr)=>{

        // تحويل كل منتج لكرت HTML ثم دمجهم بسلسلة واحدة داخل listEl
        listEl.innerHTML = arr.map(card).join('');

        // تفعيل reveal على العناصر الجديدة
        watchReveal();
      };

      // رسم المنتجات لأول مرة
      window.__renderProducts(items);

    } catch(e){

      // إذا صار خطأ: نعرض رسالة
      listEl.innerHTML = '<p class="muted">تعذر تحميل المنتجات.</p>';

      // طباعة الخطأ في الكونسول للتصحيح
      console.error(e);
    }
  }

  // حدث click عام على document (event delegation) حتى يشمل العناصر التي تُولد ديناميكيًا
  document.addEventListener('click', async (e)=>{

    // محاولة إيجاد أقرب عنصر تم الضغط عليه يحمل data-del
    const del = e.target.closest('[data-del]');

    // إذا وُجد زر حذف والمستخدم أدمن
    if (del && IS_ADMIN) {

      // جلب id من data-del
      const id = del.getAttribute('data-del');

      // نافذة تأكيد قبل الحذف
      if (confirm(t('del') + ' #' + id + '؟')) {

        // try/catch للحذف
        try {

          // إرسال DELETE للـ API مع id بالـ query
          await fetch(
            'api/products.php?id='+encodeURIComponent(id), 
            { method:'DELETE' }
          );

          // إعادة تحميل المنتجات بعد الحذف
          await load();

          // توست نجاح
          toast('✓');

        } catch(err){ 

          // توست فشل
          toast(t('failed')); 
        }
      }
    }

    // محاولة إيجاد أقرب عنصر تم الضغط عليه يحمل data-edit
    const edit = e.target.closest('[data-edit]');

    // إذا وُجد زر تعديل والمستخدم أدمن
    if (edit && IS_ADMIN) {

      // جلب id من data-edit
      const id = edit.getAttribute('data-edit');

      // جلب الكرت الأب للمنتج (حتى نقرأ منه الاسم والسعر…)
      const cardEl = edit.closest('.product');

      // قراءة الاسم الحالي من h3 (?. لتجنب الخطأ إذا العنصر غير موجود)
      const curName = cardEl.querySelector('h3')?.textContent || '';

      // قراءة السعر الحالي من strong ثم فصل الرقم عن USD
      const curPrice = (cardEl.querySelector('strong')?.textContent || '0').split(' ')[0];

      // قراءة المخزون الحالي من النص muted ثم إزالة أي شيء غير أرقام بالـ regex
      const curStock = (cardEl.querySelector('.muted')?.textContent || '').replace(/\D+/g, '') || '0';

      // قراءة رابط الصورة الحالي من img
      const curImg = cardEl.querySelector('img')?.getAttribute('src') || '';

      // prompt لاسم المنتج (إذا المستخدم ضغط Cancel ترجع null)
      const name = prompt('اسم المنتج', curName);
      if (name == null) return;

      // prompt للسعر
      const price = prompt('السعر', curPrice);
      if (price == null) return;

      // prompt للمخزون
      const stock = prompt('المخزون', curStock);
      if (stock == null) return;

      // prompt لرابط الصورة (اختياري) مع تنظيف picsum
      const image_url = prompt(
        'رابط الصورة (اختياري)', 
        curImg.includes('picsum.photos') ? '' : curImg
      );

      // إرسال التعديل للسيرفر
      try {

        // إنشاء FormData لإرسال بيانات POST بسهولة
        const f = new FormData();

        // إضافة id
        f.append('id', id);

        // إضافة الاسم
        f.append('name', name);

        // إضافة السعر
        f.append('price', price);

        // إضافة المخزون
        f.append('stock', stock);

        // إضافة رابط الصورة (أو فارغ)
        f.append('image_url', image_url || '');

        // إرسال POST إلى api/products.php
        await fetch('api/products.php', { 
          method:'POST', 
          body:f 
        });

        // إعادة تحميل المنتجات بعد التعديل
        await load();

        // توست نجاح
        toast('✓');

      } catch(err){ 

        // توست فشل
        toast(t('failed')); 
      }
    }

    // محاولة إيجاد زر يحمل data-addcart (للإشعار فقط)
    const addCart = e.target.closest('[data-addcart]');

    // إذا تم الضغط عليه نعرض توست "تمت الإضافة"
    if (addCart) toast(t('added'));
  });

  // حدث submit على كل الفورمات (حتى لو في صفحات ثانية)
  document.addEventListener('submit', (e)=>{

    // تخزين الفورم الذي تم إرساله
    const form = e.target;

    // قراءة action للفورم وتحويله لحروف صغيرة
    const action = (form.getAttribute('action')||'').toLowerCase();

    // إذا الـ action يحتوي كلمات تدل على دخول أو طلب → نعرض توست مناسب
    if (/(login|user_login|checkout|place_order|cod_place_order|orders\.php)/.test(action)) {

      // عرض توست: إذا هو login → logged، غير ذلك → placed
      toast(
        /login|user_login/.test(action) 
          ? t('logged') 
          : t('placed'), 
        1500
      );
    }

  // true: استخدام capture phase حتى نلتقط submit قبل ما الصفحة تتحول بسرعة
  }, true);

  // عند اكتمال تحميل DOM (العناصر HTML جاهزة)
  document.addEventListener('DOMContentLoaded', ()=>{

    // إضافة الهيدر والتوست والمودال
    injectChrome();

    // تحميل المنتجات من API ورسمها
    load();

    // تفعيل reveal على العناصر الحالية
    watchReveal();

    // إعادة تشغيل reveal بعد 60ms للتأكد من العناصر المتأخرة
    setTimeout(watchReveal, 60);
  });

// إغلاق وتنفيذ IIFE مباشرة
})();
