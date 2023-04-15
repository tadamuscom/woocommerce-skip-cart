const form              = document.getElementById('smwoo-settings-form')
const skipCart          = document.getElementById('smwoo-skip-cart')
const clearCart         = document.getElementById('smwoo-clear-cart')
const completeOrder     = document.getElementById('smwoo-complete-order')
const nonce             = document.getElementById('smwoo-settings-nonce')
const action            = document.getElementById('smwoo-settings-action')
const notificationWrap   = document.getElementById ('smwoo-notification-wrap')
const notificationParagraph   = document.getElementById ('smwoo-notification-paragraph')

form.addEventListener('submit', (e) => {
    e.preventDefault()

    let skip        = false;
    let clear       = false;
    let complete    = false;

    if(skipCart.checked) skip           = true
    if(clearCart.checked) clear         = true
    if(completeOrder.checked) complete  = true

    notificationWrap.style.display = 'none'
    notificationWrap.classList = ''

    jQuery.ajax({
        type    : "post",
        dataType: 'json',
        url     : smAjax.ajaxurl,
        data    : {
            action  : action.value,
            nonce   : nonce.value,
            skip    : skip,
            clear   : clear,
            complete: complete
        },
        success: function(response) {
            if(response.success){
                notificationWrap.classList.add('smwoo-notification-success')
                notificationWrap.innerText      = response.data.message
                notificationWrap.style.display  = 'inherit'
            }
        }
    })
})