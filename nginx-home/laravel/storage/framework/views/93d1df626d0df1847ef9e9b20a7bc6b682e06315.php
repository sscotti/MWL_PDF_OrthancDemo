<?php if($cookieConsentConfig['enabled'] && ! $alreadyConsentedWithCookies): ?>

    <?php echo $__env->make('cookie-consent::dialogContents', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script nonce= "<?php echo e(csp_nonce()); ?>">

        window.laravelCookieConsent = (function () {

            const COOKIE_VALUE = 1;
            const COOKIE_DOMAIN = '<?php echo e(config('session.domain') ?? request()->getHost()); ?>';

            function consentWithCookies() {
                setCookie('<?php echo e($cookieConsentConfig['cookie_name']); ?>', COOKIE_VALUE, <?php echo e($cookieConsentConfig['cookie_lifetime']); ?>);
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');

                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/<?php echo e(config('session.secure') ? ';secure' : null); ?>'
                    + '<?php echo e(config('session.same_site') ? ';samesite='.config('session.same_site') : null); ?>';
            }

            if (cookieExists('<?php echo e($cookieConsentConfig['cookie_name']); ?>')) {
                hideCookieDialog();
            }

            const buttons = document.getElementsByClassName('js-cookie-consent-agree');

            for (let i = 0; i < buttons.length; ++i) {
                buttons[i].addEventListener('click', consentWithCookies);
            }

            return {
                consentWithCookies: consentWithCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

<?php endif; ?>
<?php /**PATH /nginx-home/laravel/resources/views/vendor/cookie-consent/index.blade.php ENDPATH**/ ?>