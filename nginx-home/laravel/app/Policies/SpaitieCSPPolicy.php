<?php

namespace App\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Basic;

class SpaitieCSPPolicy extends Basic
{
    public function configure()
    {
        parent::configure();
        
        $this->addDirective(Directive::SCRIPT, 'https://www.google.com cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net unpkg.com unsafe-eval')
        ->addDirective(Directive::STYLE, 'www.google.com cdn.datatables.net unpkg.com unsafe-inline self')
        ->addDirective(Directive::FONT, 'unpkg.com self')
        ->addDirective(Directive::FRAME, 'www.google.com self')
        ->addDirective(Directive::IMG, 'data: blob: *');

    }
}


// namespace Spatie\Csp\Policies;
// 
// use Spatie\Csp\Directive;
// use Spatie\Csp\Keyword;
// 
// class Basic extends Policy
// {
//     public function configure()
//     {
//         $this
//             ->addDirective(Directive::BASE, Keyword::SELF)
//             ->addDirective(Directive::CONNECT, Keyword::SELF)
//             ->addDirective(Directive::DEFAULT, Keyword::SELF)
//             ->addDirective(Directive::FORM_ACTION, Keyword::SELF)
//             ->addDirective(Directive::IMG, Keyword::SELF)
//             ->addDirective(Directive::MEDIA, Keyword::SELF)
//             ->addDirective(Directive::OBJECT, Keyword::NONE)
//             ->addDirective(Directive::SCRIPT, Keyword::SELF)
//             ->addDirective(Directive::STYLE, Keyword::SELF)
//             ->addNonceForDirective(Directive::SCRIPT)
//             ->addNonceForDirective(Directive::STYLE);
//     }
// }

/*
    const BASE = 'base-uri';
    const BLOCK_ALL_MIXED_CONTENT = 'block-all-mixed-content';
    const CHILD = 'child-src';
    const CONNECT = 'connect-src';
    const DEFAULT = 'default-src';
    const FONT = 'font-src';
    const FORM_ACTION = 'form-action';
    const FRAME = 'frame-src';
    const FRAME_ANCESTORS = 'frame-ancestors';
    const IMG = 'img-src';
    const MANIFEST = 'manifest-src';
    const MEDIA = 'media-src';
    const OBJECT = 'object-src';
    const PLUGIN = 'plugin-types';
    const PREFETCH = 'prefetch-src';
    const REPORT = 'report-uri';
    const REPORT_TO = 'report-to';
    const SANDBOX = 'sandbox';
    const SCRIPT = 'script-src';
    const SCRIPT_ATTR = 'script-src-attr';
    const SCRIPT_ELEM = 'script-src-elem';
    const STYLE = 'style-src';
    const STYLE_ATTR = 'style-src-attr';
    const STYLE_ELEM = 'style-src-elem';
    const UPGRADE_INSECURE_REQUESTS = 'upgrade-insecure-requests';
    const WEB_RTC = 'webrtc-src';
    const WORKER = 'worker-src';
    
*/