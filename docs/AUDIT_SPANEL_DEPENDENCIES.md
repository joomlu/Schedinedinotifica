# Auditoría de dependencias SPanel

Consulta online: 2026-09-29. Sin actualizar locks. Los avisos son del inventario de dependencias; no prueban por sí solos explotabilidad de cada ruta.

Comandos: `composer audit --locked --no-dev --format=json` y `npm audit --json`.

## Composer: 42 avisos, 11 paquetes de producción

| Paquete | Versión | Severidad | Aviso |
|---|---|---|---|
| guzzlehttp/guzzle | 7.9.2 | high | [PKSA-gcrk-3vtt-1r14: Guzzle: Noncanonical host can bypass host-based checks](https://github.com/advisories/GHSA-v5mv-p594-2x33) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-cnw1-2ytm-cgr8: Guzzle: Noncanonical cookie domain keeps subdomain scope](https://github.com/advisories/GHSA-f7vp-7xgx-4w4r) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-fy2t-3c5f-827y: Guzzle: URI fragments disclosed in redirect Referer headers](https://github.com/advisories/GHSA-h95v-h523-3mw8) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-qxvb-2bpp-dnk6: Guzzle: Host-only cookie scope is not preserved](https://github.com/advisories/GHSA-wm3w-8rrp-j577) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-bbs6-q5q9-f3t4: Guzzle: Unbounded response cookies risk denial of service](https://github.com/advisories/GHSA-f283-ghqc-fg79) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-bcdd-5xc7-gwfb: Guzzle: Cookie Disclosure and Injection via IP-Address Domains](https://github.com/advisories/GHSA-g446-98w2-8p5w) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-pwsk-hy21-4gby: Guzzle: Proxy-Authorization headers can be sent to origin servers](https://github.com/advisories/GHSA-94pj-82f3-465w) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-93qv-9n9h-6k6p: Dot-only cookie domains match all hosts](https://github.com/guzzle/guzzle/security/advisories/GHSA-cwxw-98qj-8qjx) |
| guzzlehttp/guzzle | 7.9.2 | medium | [PKSA-k22t-f949-t9g6: Silent HTTPS proxy downgrade to cleartext](https://github.com/guzzle/guzzle/security/advisories/GHSA-wpwq-4j6v-78m3) |
| guzzlehttp/psr7 | 2.7.0 | medium | [PKSA-vznr-tgp9-fd7d: guzzlehttp/psr7: Host Confusion via Weak URI Host Validation](https://github.com/advisories/GHSA-c2w2-prh8-qm98) |
| guzzlehttp/psr7 | 2.7.0 | medium | [PKSA-7qs6-zvnz-h66r: CRLF injection in HTTP start-line serialization](https://github.com/guzzle/psr7/security/advisories/GHSA-vm85-hxw5-5432) |
| guzzlehttp/psr7 | 2.7.0 | medium | [PKSA-gm5x-j3mz-71n9: CRLF injection via URI host component](https://github.com/guzzle/psr7/security/advisories/GHSA-hq7v-mx3g-29hw) |
| guzzlehttp/psr7 | 2.7.0 | medium | [PKSA-jj5t-2zs1-dcfm: Host confusion via authority reinterpretation](https://github.com/guzzle/psr7/security/advisories/GHSA-34xg-wgjx-8xph) |
| laravel/framework | v11.31.0 | medium | [PKSA-m5cs-t1y6-qpcs: Laravel Framework: Temporary Signed URL Path Confusion](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) |
| laravel/framework | v11.31.0 | high | [PKSA-3r5d-mb8f-1qw9: Laravel Framework: CRLF injection in default email rule ](https://github.com/advisories/GHSA-5vg9-5847-vvmq) |
| laravel/framework | v11.31.0 | no indicada | [PKSA-mdq4-51ck-6kdq: Laravel CRLF injection in default email rule](https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq) |
| laravel/framework | v11.31.0 | medium | [PKSA-8qx3-n5y5-vvnd: Laravel has a File Validation Bypass](https://github.com/advisories/GHSA-78fx-h6xr-vch4) |
| laravel/framework | v11.31.0 | medium | [PKSA-q46n-4fdk-zjr4: Laravel Reflected XSS via Route Parameter in Debug-Mode Error Page](https://github.com/sbaresearch/advisories/tree/public/2024/SBA-ADV-20241209-02_Laravel_Reflected_XSS_via_Route_Parameter_in_Debug-Mode_Error_Page) |
| laravel/framework | v11.31.0 | medium | [PKSA-qzrn-rnz3-85w1: Laravel Reflected XSS via Request Parameter in Debug-Mode Error Page](https://github.com/sbaresearch/advisories/tree/public/2024/SBA-ADV-20241209-01_Laravel_Reflected_XSS_via_Request_Parameter_in_Debug-Mode_Error_Page) |
| league/commonmark | 2.5.3 | high | [PKSA-zyf5-hrxv-hrd7: league/commonmark: Denial of service via distinctly-named attributes in the Attributes extension](https://github.com/advisories/GHSA-8rr7-cvq3-gmfh) |
| league/commonmark | 2.5.3 | high | [PKSA-nv44-1b4d-6gjg: league/commonmark: Denial of service in the SmartPunct and Attributes extensions](https://github.com/advisories/GHSA-jjv6-8j6v-6j52) |
| league/commonmark | 2.5.3 | high | [PKSA-9q1p-3s19-bp1q: league/commonmark: Denial of service via crafted code fences, reference links, and emphasis delimiters](https://github.com/advisories/GHSA-j8pm-gj4c-rq4x) |
| league/commonmark | 2.5.3 | medium | [PKSA-5mzr-szzf-z6cn: league/commonmark: Denial of service via deeply nested XML output](https://github.com/advisories/GHSA-mj63-m3rc-8ppr) |
| league/commonmark | 2.5.3 | high | [PKSA-cqd6-fg4n-nxpf: league/commonmark: Denial of service via colliding heading slugs](https://github.com/advisories/GHSA-mh25-x5hq-wrqp) |
| league/commonmark | 2.5.3 | high | [PKSA-1q6p-sqkj-8mmj: league/commonmark:  Denial of service via duplicate footnote definitions](https://github.com/advisories/GHSA-jfm3-95jq-q3rf) |
| league/commonmark | 2.5.3 | high | [PKSA-mc58-w91n-f5gv: league/commonmark: Denial of service via adjacent inline attribute blocks](https://github.com/advisories/GHSA-g2gp-3wwq-f4ph) |
| league/commonmark | 2.5.3 | high | [PKSA-t21r-vtr5-3mdz: league/commonmark: Quadratic-time denial of service when parsing crafted Markdown](https://github.com/advisories/GHSA-2q4p-g7hv-5rgv) |
| league/commonmark | 2.5.3 | medium | [PKSA-scnn-p8mm-jbft: league/commonmark: AttributesExtension href/src unsafe-link filter bypass via embedded control bytes](https://github.com/advisories/GHSA-29pj-957v-52mc) |
| league/commonmark | 2.5.3 | medium | [PKSA-21fb-n1x5-5nf7: league/commonmark has an embed extension allowed_domains bypass](https://github.com/advisories/GHSA-hh8v-hgvp-g3f5) |
| league/commonmark | 2.5.3 | medium | [PKSA-2cx9-ynrq-qdk3: CommonMark has DisallowedRawHtml extension bypass via whitespace in HTML tag names](https://github.com/advisories/GHSA-4v6x-c7xx-hw9f) |
| league/commonmark | 2.5.3 | medium | [PKSA-rqc2-tcc6-nc79: league/commonmark contains a XSS vulnerability in Attributes extension](https://github.com/advisories/GHSA-3527-qv2q-pfvx) |
| league/commonmark | 2.5.3 | high | [PKSA-fndg-qryc-dyc9: league/commonmark's quadratic complexity bugs may lead to a denial of service](https://github.com/advisories/GHSA-c2pc-g5qf-rfrf) |
| nesbot/carbon | 3.8.2 | medium | [PKSA-csyb-yc4p-mnbs: Carbon has an arbitrary file include via unvalidated input passed to Carbon::setLocale](https://github.com/advisories/GHSA-j3f9-p6hm-5w6q) |
| psy/psysh | v0.12.4 | medium | [PKSA-4s4z-t146-6123: PsySH has Local Privilege Escalation via CWD .psysh.php auto-load](https://github.com/advisories/GHSA-4486-gxhx-5mg7) |
| symfony/http-foundation | v7.1.7 | medium | [PKSA-y6py-qpv1-h52p: CVE-2026-48736: IpUtils::PRIVATE_SUBNETS Omits IPv6 Transition Forms (6to4, NAT64, Teredo, IPv4-compatible): SSRF Bypass in NoPrivateNetworkHttpClient](https://symfony.com/cve-2026-48736) |
| symfony/http-foundation | v7.1.7 | high | [PKSA-365x-2zjk-pt47: CVE-2025-64500: Incorrect parsing of PATH_INFO can lead to limited authorization bypass](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) |
| symfony/mailer | v7.1.6 | medium | [PKSA-28rh-rzzn-djk4: CVE-2026-45068: Argument Injection in SendmailTransport via Dash-Prefixed Recipient Address](https://symfony.com/cve-2026-45068) |
| symfony/mime | v7.1.6 | medium | [PKSA-wtxr-p26d-nn42: CVE-2026-45070: Email Header Injection via Non-Token Characters in Mime Parameter Names](https://symfony.com/cve-2026-45070) |
| symfony/mime | v7.1.6 | high | [PKSA-2n2k-66v2-bwg3: CVE-2026-45067: Email Header / SMTP Command Injection via CRLF in Symfony\Component\Mime\Address](https://symfony.com/cve-2026-45067) |
| symfony/polyfill-intl-idn | v1.31.0 | low | [PKSA-dwsq-ppd2-mb1x: CVE-2026-46644: symfony/polyfill-intl-idn accepts xn-- labels whose Punycode payload decodes to ASCII-only: insecure equivalence](https://symfony.com/cve-2026-46644) |
| symfony/routing | v7.1.6 | medium | [PKSA-bf7t-jnpz-492k: CVE-2026-48784: UrlGenerator Dot-Segment Encoding Skips Every Other Chained `../` or `./` → Generated URL Collapses Off-Route Under RFC 3986 Normalization](https://symfony.com/cve-2026-48784) |
| symfony/routing | v7.1.6 | medium | [PKSA-yc7t-91v9-99xs: CVE-2026-45065: UrlGenerator Route-Requirement Bypass via Unanchored Regex Alternation → Off-Site //host URL Injection](https://symfony.com/cve-2026-45065) |

## npm: 61 dependencias afectadas

Incluye dependencias transitivas y de desarrollo: 2 críticas, 17 altas, 41 moderadas y 1 baja. Vite copia bibliotecas a public/build/libs, por lo que no basta con descartar todos los avisos frontend como dev-only.

| Paquete | Severidad | Directa | Corrección informada por npm |
|---|---|---|---|
| @ckeditor/ckeditor5-adapter-ckfinder | moderate | no | true |
| @ckeditor/ckeditor5-autoformat | moderate | no | true |
| @ckeditor/ckeditor5-basic-styles | moderate | no | true |
| @ckeditor/ckeditor5-block-quote | moderate | no | true |
| @ckeditor/ckeditor5-build-classic | moderate | sí | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| @ckeditor/ckeditor5-ckbox | moderate | no | true |
| @ckeditor/ckeditor5-ckfinder | moderate | no | true |
| @ckeditor/ckeditor5-clipboard | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| @ckeditor/ckeditor5-cloud-services | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| @ckeditor/ckeditor5-core | moderate | no | true |
| @ckeditor/ckeditor5-easy-image | moderate | no | true |
| @ckeditor/ckeditor5-editor-classic | moderate | no | true |
| @ckeditor/ckeditor5-engine | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| @ckeditor/ckeditor5-enter | moderate | no | true |
| @ckeditor/ckeditor5-essentials | moderate | no | true |
| @ckeditor/ckeditor5-heading | moderate | no | true |
| @ckeditor/ckeditor5-image | moderate | no | true |
| @ckeditor/ckeditor5-indent | moderate | no | true |
| @ckeditor/ckeditor5-link | moderate | no | true |
| @ckeditor/ckeditor5-list | moderate | no | true |
| @ckeditor/ckeditor5-media-embed | moderate | no | true |
| @ckeditor/ckeditor5-paragraph | moderate | no | true |
| @ckeditor/ckeditor5-paste-from-office | moderate | no | true |
| @ckeditor/ckeditor5-select-all | moderate | no | true |
| @ckeditor/ckeditor5-table | moderate | no | true |
| @ckeditor/ckeditor5-typing | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| @ckeditor/ckeditor5-ui | moderate | no | true |
| @ckeditor/ckeditor5-undo | moderate | no | true |
| @ckeditor/ckeditor5-upload | moderate | no | true |
| @ckeditor/ckeditor5-utils | moderate | no | true |
| @ckeditor/ckeditor5-watchdog | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| @ckeditor/ckeditor5-widget | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| ajv | moderate | no | true |
| axios | high | sí | true |
| brace-expansion | high | no | true |
| braces | high | no | true |
| browserslist | high | no | true |
| ckeditor5 | moderate | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| cross-spawn | high | no | true |
| echarts | moderate | sí | {"name": "echarts", "version": "6.1.0", "isSemVerMajor": true} |
| esbuild | moderate | no | true |
| follow-redirects | moderate | no | true |
| form-data | critical | no | true |
| glob | high | no | true |
| immutable | high | no | true |
| lodash | high | sí | true |
| lodash-es | high | no | {"name": "@ckeditor/ckeditor5-build-classic", "version": "39.0.2", "isSemVerMajor": true} |
| minimatch | high | no | true |
| moment | high | sí | {"name": "moment", "version": "2.31.0", "isSemVerMajor": false} |
| nanoid | high | no | true |
| picomatch | high | no | true |
| postcss | high | sí | true |
| prismjs | moderate | sí | true |
| quill | moderate | sí | {"name": "quill", "version": "2.0.3", "isSemVerMajor": true} |
| rollup | high | no | true |
| serialize-javascript | high | no | true |
| sweetalert2 | low | sí | true |
| swiper | critical | sí | {"name": "swiper", "version": "14.3.0", "isSemVerMajor": true} |
| terser-webpack-plugin | moderate | no | true |
| vite | high | sí | true |
| webpack | moderate | no | true |

## Acciones pendientes

Revisar alcanzabilidad, actualizar dependencias en una rama específica y probar auth, validación, mail, XML/SOAP, imports y componentes JS. Planificar salto a una versión Laravel soportada: Laravel 11 terminó soporte el 12 de marzo de 2026 ([política oficial](https://laravel.com/docs/11.x/releases)). No hacer actualizaciones forzadas ni ignorar avisos para declarar la aplicación segura. Repetir las auditorías al aprobar la salida.
