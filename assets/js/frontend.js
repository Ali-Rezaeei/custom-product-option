
(function($){
  function getAll(){
    try { return JSON.parse($('#cpo-data-json').text() || '{}'); }
    catch(e){ return {}; }
  }

  function openModalForField(fieldKey){
    var all = getAll();
    var f = all[fieldKey];
    if(!f) return;

    $('#cpoModalTitle').text(f.label || '');

    var $tabs = $('.cpo-modal .cpo-modal-tabs').empty();
    var $grid = $('.cpo-modal .cpo-options-grid').empty();

    if(Array.isArray(f.groups) && f.groups.length){
      f.groups.forEach(function(g, i){
        var $btn = $('<button type="button" class="cpo-tab"></button>')
            .text(g.label || '')
            .attr('data-gkey', g.key || '');
        $btn.on('click', function(){
          $('.cpo-tab').removeClass('active');
          $(this).addClass('active');
          renderGroupValues(g, fieldKey);
        });
        $tabs.append($btn);
      });
      $tabs.find('.cpo-tab').first().trigger('click');
    }else{
      $grid.append('<em>گروهی تعریف نشده است.</em>');
    }

    $('.cpo-modal').fadeIn(120).attr('aria-hidden','false').data('field-key', fieldKey);
  }

  function renderGroupValues(group, fieldKey){
    var $grid = $('.cpo-modal .cpo-options-grid').empty();
    if(Array.isArray(group.values) && group.values.length){
      group.values.forEach(function(v){
        var $card = $('<button type="button" class="cpo-card"></button>').attr('data-key', v.key);
        if(v.thumb){
          $card.append('<span class="cpo-thumb"><img src="'+ v.thumb +'" alt="'+ (v.label || '') +'"></span>');
        }else if(v.color){
          $card.append('<span class="cpo-swatch" style="background:'+ v.color +'"></span>');
        }else{
          $card.append('<span class="cpo-swatch cpo-empty"></span>');
        }
        var code = v.key ? ('کد: ' + v.key) : '';
        var $meta = $('<div class="cpo-card-meta"></div>')
            .append('<div class="cpo-card-label">'+ (v.label || '') +'</div>')
            .append('<div class="cpo-card-code">'+ code +'</div>');
        $card.append($meta);

        $card.on('click', function(){
          applyChoice(fieldKey, group, v);
          closeModal();
        });

        $grid.append($card);
      });
    }else{
      $grid.append('<em>گزینه‌ای برای این تب ثبت نشده است.</em>');
    }
  }

  function closeModal(){
    $('.cpo-modal').fadeOut(120).attr('aria-hidden','true').removeData('field-key');
  }

  function applyChoice(fieldKey, group, v){
    // Hidden inputs برای POST
    $('input[name="cpo_choice['+fieldKey+']"]').val(v.key || '');
    $('input[name="cpo_choice_group['+fieldKey+']"]').val(group.key || '');

    var $row = $('.cpo-inline-row[data-key="'+fieldKey+'"]');
    var $sw  = $row.find('.cpo-preview-swatch').empty();
    var $cd  = $row.find('.cpo-preview-code');

    if(v.thumb){
      var $img = $('<img>', {
      src: v.thumb,
      alt: v.label || '',
      class: 'cpo-zoomable'
  });
  $img.on('click', function(){
      $('.cpo-image-preview img').attr('src', v.thumb);
      $('.cpo-image-preview').fadeIn(150);
  });
  $sw.html($img);
  $row.addClass('cpo-has-image').removeClass('cpo-has-color');
    }else if(v.color){
      $sw.css('background', v.color);
      $row.addClass('cpo-has-color').removeClass('cpo-has-image');
    }else{
      $sw.attr('style','');
    }
    $cd.text(v.key ? ('کد: ' + v.key) : 'کد: —');
  }

  $(document).on('click', '.cpo-open-modal', function(){
    openModalForField($(this).data('key'));
  });
  $(document).on('click', '.cpo-close, .cpo-modal-backdrop', function(){
    closeModal();
  });
  $(document).on('click', '.cpo-image-preview, .cpo-image-backdrop', function(){
    $('.cpo-image-preview').fadeOut(150);
});
$(document).on('click', '.cpo-image-close, .cpo-image-backdrop', function(){
    $('.cpo-image-preview').fadeOut(150);
});

})(jQuery);

