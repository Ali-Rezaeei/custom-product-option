(function($){
  var KEY_RE = /^[a-z0-9_-]+$/i;

  function markValid($inp){ $inp.removeClass('cpo-invalid'); $inp.siblings('.cpo-error').text(''); }
  function markInvalid($inp){ $inp.addClass('cpo-invalid'); $inp.siblings('.cpo-error').text('فقط a-z, 0-9, -, _'); }

  function validateKey($inp){
    var v = ($inp.val() || '').trim();
    if(!v || !KEY_RE.test(v)){ markInvalid($inp); return false; }
    markValid($inp); return true;
  }

  function tplField(fi){
    return $(`
      <div class="cpo-field" data-fi="${fi}">
        <div class="cpo-field-head">
          <div>
            <label>عنوان فیلد</label>
            <input type="text" name="cpo[fields][${fi}][label]" required />
          </div>
          <div>
            <label>کلید فیلد (انگلیسی)</label>
            <input type="text" class="cpo-key" pattern="[A-Za-z0-9_-]+" title="فقط a-z, 0-9, -, _" name="cpo[fields][${fi}][key]" required placeholder="code-100" />
            <small class="cpo-error"></small>
          </div>
          <div class="cpo-inline">
            <label><input type="checkbox" name="cpo[fields][${fi}][required]" value="1" /> اجباری</label>
          </div>
          <button type="button" class="cpo-icon-btn cpo-remove-field" aria-label="حذف فیلد">
            <span class="dashicons dashicons-trash"></span>
          </button>
        </div>
        <div class="cpo-groups"></div>
        <p><button type="button" class="button cpo-add-group" data-fi="${fi}">افزودن گروه (تب)</button></p>
      </div>
    `);
  }

  function tplGroup(fi, gi){
    return $(`
      <div class="cpo-group" data-gi="${gi}">
        <div class="cpo-group-head">
          <div>
            <label>لیبل گروه (تب)</label>
            <input type="text" name="cpo[fields][${fi}][groups][${gi}][label]" required />
          </div>
          <div>
            <label>کلید گروه (انگلیسی)</label>
            <input type="text" class="cpo-key" pattern="[A-Za-z0-9_-]+" title="فقط a-z, 0-9, -, _" name="cpo[fields][${fi}][groups][${gi}][key]" required placeholder="code-100" />
            <small class="cpo-error"></small>
          </div>
          <button type="button" class="cpo-icon-btn cpo-remove-group" aria-label="حذف گروه">
            <span class="dashicons dashicons-no-alt"></span>
          </button>
        </div>
        <div class="cpo-values">
          <table class="widefat fixed">
            <thead>
              <tr>
                <th>لیبل مقدار</th>
                <th>کلید مقدار/کُد (انگلیسی)</th>
                <th>تصویر</th>
                <th>کد رنگ (HEX)</th>
                <th></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <p><button type="button" class="button cpo-add-value" data-fi="${fi}" data-gi="${gi}">افزودن مقدار</button></p>
        </div>
      </div>
    `);
  }

  function tplValue(fi, gi, vi){
    return $(`
      <tr class="cpo-value" data-vi="${vi}">
        <td><input type="text" name="cpo[fields][${fi}][groups][${gi}][values][${vi}][label]" required /></td>
        <td>
          <input type="text" class="cpo-key" pattern="[A-Za-z0-9_-]+" title="فقط a-z, 0-9, -, _" name="cpo[fields][${fi}][groups][${gi}][values][${vi}][key]" required />
          <small class="cpo-error"></small>
        </td>
        <td>
          <div class="cpo-image">
            <input type="hidden" class="cpo-image-id" name="cpo[fields][${fi}][groups][${gi}][values][${vi}][image_id]" value="" />
            <button type="button" class="button cpo-upload">انتخاب تصویر</button>
            <div class="cpo-thumb"></div>
          </div>
        </td>
        <td><input type="text" class="cpo-color" name="cpo[fields][${fi}][groups][${gi}][values][${vi}][color]" placeholder="#RRGGBB" /></td>
        <td>
          <button type="button" class="cpo-icon-btn cpo-remove-value" aria-label="حذف مقدار">
            <span class="dashicons dashicons-minus"></span>
          </button>
        </td>
      </tr>
    `);
  }

  function bindField($f){
    $f.on('click', '.cpo-remove-field', function(e){ e.preventDefault(); $f.remove(); });
    $f.on('click', '.cpo-add-group', function(e){
      e.preventDefault();
      var fi = $(this).data('fi');
      var $wrap = $f.find('.cpo-groups');
      var gi = $wrap.find('.cpo-group').length;
      var $g = tplGroup(fi, gi);
      bindGroup($g);
      $wrap.append($g);
    });
    $f.find('.cpo-key').on('input blur', function(){ validateKey($(this)); });
  }

  function bindGroup($g){
    $g.on('click', '.cpo-remove-group', function(e){ e.preventDefault(); $g.remove(); });
    $g.on('click', '.cpo-add-value', function(e){
      e.preventDefault();
      var fi = $(this).data('fi');
      var gi = $(this).data('gi');
      var $tbody = $g.find('tbody');
      var vi = $tbody.find('tr').length;
      var $v = tplValue(fi, gi, vi);
      bindValue($v);
      $tbody.append($v);
    });
    $g.find('.cpo-key').on('input blur', function(){ validateKey($(this)); });
  }

  function bindValue($v){
    $v.on('click', '.cpo-remove-value', function(e){ e.preventDefault(); $v.remove(); });

    $v.on('click', '.cpo-upload', function(e){
      e.preventDefault();
      var frame = wp.media({ title: 'انتخاب یا آپلود تصویر', button: { text: 'استفاده از این تصویر' }, multiple:false });
      frame.on('select', function(){
        var att = frame.state().get('selection').first().toJSON();
        $v.find('.cpo-image-id').val(att.id);
        $v.find('.cpo-thumb').html('<img src="'+ (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) +'" />');
        $v.find('.cpo-color').val('');
      });
      frame.open();
    });

    $v.on('input', '.cpo-color', function(){
      var val = $(this).val().trim();
      if(val.length){
        $v.find('.cpo-image-id').val('');
        $v.find('.cpo-thumb').empty();
      }
    });

    $v.find('.cpo-key').on('input blur', function(){ validateKey($(this)); });
  }

  $(document).ready(function(){
    $('#cpo-add-field').on('click', function(){
      var fi = $('#cpo-fields .cpo-field').length;
      var $f = tplField(fi);
      bindField($f);
      $('#cpo-fields').append($f);
    });

    // bind موجود
    $('.cpo-field').each(function(){ bindField($(this)); });
    $('.cpo-group').each(function(){ bindGroup($(this)); });
    $('.cpo-value').each(function(){ bindValue($(this)); });

    $('#post').on('submit', function(){
      var ok = true;

      $('.cpo-key').each(function(){
        if(!validateKey($(this))){ ok = false; }
      });

      if(!ok){
        alert('لطفاً کلیدهای انگلیسی معتبر وارد کنید (a-z, 0-9, -, _).');
        return false;
      }
      return true;
    });
  });
})(jQuery);
