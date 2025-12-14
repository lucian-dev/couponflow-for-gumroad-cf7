(function ($) {
  $(document).ready(function () {
    const $checkBtn = $('#cf7g_check_connection');
    const $resetBtn = $('#cf7g_reset_connection');

    // Reusable spinner overlay.
    const showSpinner = (btn, show = true) => {
      if (show) {
        btn.after('<span class="spinner is-active" style="margin-left:4px;"></span>');
        btn.prop('disabled', true);
      } else {
        btn.siblings('.spinner').remove();
        btn.prop('disabled', false);
      }
    };

    // Handle 'Check Connection'.
    $checkBtn.on('click', function (e) {
      e.preventDefault();
      const nonce = $(this).data('nonce');
      const formId = $(this).data('form');

      showSpinner($checkBtn, true);

      $.post(CGC.ajax, {
        action: 'cf7g_check_connection',
        nonce: nonce,
        form_id: formId,
      })
        .done(function (res) {
          showSpinner($checkBtn, false);
          if (res.success) {
            alert(res.data.message || 'Connection successfull!');
            location.reload();
          } else {
            alert(res.data?.message || 'Connection failed.');
          }
        })
        .fail(function (xhr) {
          alert(xhr.responseJSON?.data?.message || 'Request failed.');
        })
        .always(function () {
          showSpinner($checkBtn, false);
        });
    });

    // Handle 'Reset Connection'.
    $resetBtn.on('click', function (e) {
      e.preventDefault();
      const nonce = $(this).data('nonce');

      if (!confirm('Are you sure you want to reset your Gumroad connection?')) {
        return;
      }

      showSpinner($resetBtn, true);

      $.post(CGC.ajax, {
        action: 'cf7g_reset_connection',
        nonce: nonce,
      })
        .done(function (res) {
          showSpinner($resetBtn, false);
          if (res.success) {
            alert(res?.data?.message || '🔄 Connection reset successful.');
            location.reload();
          } else {
            alert(res?.data?.message || 'Reset failed.');
          }
        })
        .fail(function (xhr) {
          alert(xhr.responseJSON?.data?.message || 'Request failed.');
        })
        .always(function () {
          showSpinner($resetBtn, false);
        });
    });
  });
})(jQuery);
