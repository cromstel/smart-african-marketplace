# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: service-category-pages.spec.ts >> Service Category Pages >> Sub-category pages >> loads /services/electrician-services without errors
- Location: tests\e2e\service-category-pages.spec.ts:77:7

# Error details

```
Error: expect(received).toBe(expected) // Object.is equality

Expected: 200
Received: 500
```

# Page snapshot

```yaml
- generic [ref=e2]:
  - navigation [ref=e3]:
    - generic [ref=e4]:
      - generic [ref=e6]:
        - list [ref=e7]:
          - listitem [ref=e8]:
            - link [ref=e9] [cursor=pointer]:
              - /url: "#stack"
              - button "Stack" [ref=e10]
          - listitem [ref=e14]:
            - link [ref=e15] [cursor=pointer]:
              - /url: "#context"
              - button "Context" [ref=e16]
          - listitem [ref=e20]:
            - link [ref=e21] [cursor=pointer]:
              - /url: "#debug"
              - button "Debug" [ref=e22]
          - listitem [ref=e26]:
            - link [ref=e27] [cursor=pointer]:
              - /url: https://flareapp.io/?utm_campaign=ignition&utm_source=ignition
              - button "Flare" [ref=e28]
        - list [ref=e35]:
          - listitem [ref=e36]:
            - link [ref=e37] [cursor=pointer]:
              - /url: "#share"
              - button "Share" [ref=e38]
            - generic:
              - generic:
                - generic:
                  - heading "Share with Flare" [level=4]
                  - link "Docs":
                    - /url: https://flareapp.io/docs/ignition/introducing-ignition/sharing-errors?utm_campaign=ignition&utm_source=ignition
                - list:
                  - listitem:
                    - generic:
                      - checkbox "Stack" [checked]
                      - generic: Stack
                  - listitem:
                    - generic:
                      - checkbox "Context" [checked]
                      - generic: Context
                  - listitem:
                    - generic:
                      - checkbox "Debug" [checked]
                      - generic: Debug
                - generic:
                  - button "Create Share"
          - listitem [ref=e42]:
            - link [ref=e43] [cursor=pointer]:
              - /url: https://laravel.com/docs/
              - button "Docs" [ref=e44]
          - listitem [ref=e48]:
            - link [ref=e49] [cursor=pointer]:
              - /url: "#settings"
              - button [ref=e50]
            - generic:
              - generic:
                - generic:
                  - heading "Ignition Settings" [level=4]
                  - link "Docs":
                    - /url: https://flareapp.io/ignition?utm_campaign=ignition&utm_source=ignition
                - generic:
                  - text: Editor
                  - generic:
                    - combobox "Editor":
                      - option "Clipboard"
                      - option "Sublime"
                      - option "TextMate"
                      - option "Emacs"
                      - option "MacVim"
                      - option "PhpStorm" [selected]
                      - option "PHPStorm Remote"
                      - option "Idea"
                      - option "VS Code"
                      - option "VS Code Insiders"
                      - option "VS Code Remote"
                      - option "VS Code Insiders Remote"
                      - option "VS Codium"
                      - option "Cursor"
                      - option "Atom"
                      - option "Nova"
                      - option "NetBeans"
                      - option "Zed"
                      - option "Windsurf"
                      - option "Xdebug"
                - generic:
                  - text: Theme
                  - button "auto"
                - generic:
                  - button "Save settings"
                - paragraph:
                  - text: Settings will be saved locally in
                  - code: ~/.ignition.json
                  - text: .
      - link "Undefined variable $categorySlug" [ref=e56] [cursor=pointer]:
        - /url: "#top"
  - main [ref=e58]:
    - main [ref=e62]:
      - generic [ref=e64]:
        - generic [ref=e65]:
          - generic [ref=e66]: ErrorException
          - generic [ref=e69]:
            - generic [ref=e70]: PHP 8.5.10
            - generic [ref=e71]: 12.71.0
        - generic [ref=e74]: Undefined variable $categorySlug
    - generic [ref=e78]:
      - complementary [ref=e79]:
        - generic [ref=e80]:
          - button "Expand vendor frames" [ref=e82] [cursor=pointer]
          - list [ref=e89]:
            - listitem [ref=e90]:
              - generic [ref=e92]:
                - generic [ref=e93]: App
                - generic [ref=e94]: \
                - generic [ref=e95]: Http
                - generic [ref=e96]: \
                - generic [ref=e97]: Controllers
                - generic [ref=e98]: \
                - generic [ref=e99]: Public
                - generic [ref=e100]: \
                - generic [ref=e101]: ServiceController
                - generic [ref=e102]: ": 70"
              - generic [ref=e103]: byCategory
            - listitem [ref=e104]
            - listitem [ref=e105] [cursor=pointer]: 6 vendor frames
            - listitem [ref=e108] [cursor=pointer]:
              - generic [ref=e110]:
                - generic [ref=e111]: App
                - generic [ref=e112]: \
                - generic [ref=e113]: Http
                - generic [ref=e114]: \
                - generic [ref=e115]: Middleware
                - generic [ref=e116]: \
                - generic [ref=e117]: MaintenanceMode
                - generic [ref=e118]: ": 54"
              - generic [ref=e119]: handle
            - listitem [ref=e120] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e123] [cursor=pointer]:
              - generic [ref=e125]:
                - generic [ref=e126]: App
                - generic [ref=e127]: \
                - generic [ref=e128]: Http
                - generic [ref=e129]: \
                - generic [ref=e130]: Middleware
                - generic [ref=e131]: \
                - generic [ref=e132]: CompressResponse
                - generic [ref=e133]: ": 13"
              - generic [ref=e134]: handle
            - listitem [ref=e135] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e138] [cursor=pointer]:
              - generic [ref=e140]:
                - generic [ref=e141]: App
                - generic [ref=e142]: \
                - generic [ref=e143]: Http
                - generic [ref=e144]: \
                - generic [ref=e145]: Middleware
                - generic [ref=e146]: \
                - generic [ref=e147]: TrackUserBehavior
                - generic [ref=e148]: ": 14"
              - generic [ref=e149]: handle
            - listitem [ref=e150] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e153] [cursor=pointer]:
              - generic [ref=e155]:
                - generic [ref=e156]: App
                - generic [ref=e157]: \
                - generic [ref=e158]: Http
                - generic [ref=e159]: \
                - generic [ref=e160]: Middleware
                - generic [ref=e161]: \
                - generic [ref=e162]: CaptureCampaignContext
                - generic [ref=e163]: ": 31"
              - generic [ref=e164]: handle
            - listitem [ref=e165] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e168] [cursor=pointer]:
              - generic [ref=e170]:
                - generic [ref=e171]: App
                - generic [ref=e172]: \
                - generic [ref=e173]: Http
                - generic [ref=e174]: \
                - generic [ref=e175]: Middleware
                - generic [ref=e176]: \
                - generic [ref=e177]: SetLocale
                - generic [ref=e178]: ": 22"
              - generic [ref=e179]: handle
            - listitem [ref=e180] [cursor=pointer]: 14 vendor frames
            - listitem [ref=e183] [cursor=pointer]:
              - generic [ref=e185]:
                - generic [ref=e186]: App
                - generic [ref=e187]: \
                - generic [ref=e188]: Http
                - generic [ref=e189]: \
                - generic [ref=e190]: Middleware
                - generic [ref=e191]: \
                - generic [ref=e192]: TraceMiddleware
                - generic [ref=e193]: ": 24"
              - generic [ref=e194]: handle
            - listitem [ref=e195] [cursor=pointer]: 10 vendor frames
            - listitem [ref=e198] [cursor=pointer]:
              - generic [ref=e200]:
                - generic [ref=e201]: App
                - generic [ref=e202]: \
                - generic [ref=e203]: Http
                - generic [ref=e204]: \
                - generic [ref=e205]: Middleware
                - generic [ref=e206]: \
                - generic [ref=e207]: PreventCrlfInjection
                - generic [ref=e208]: ": 79"
              - generic [ref=e209]: handle
            - listitem [ref=e210] [cursor=pointer]: 18 vendor frames
            - listitem [ref=e213] [cursor=pointer]:
              - generic [ref=e215]:
                - generic [ref=e216]: C:\projects\smart-african-marketplace\public\index
                - generic [ref=e217]: .php
                - generic [ref=e218]: ": 58"
              - generic [ref=e219]: "[top]"
      - generic [ref=e220]:
        - 'link "C:\\projects\\smart-african-marketplace\\app\\Http\\Controllers\\Public\\ServiceController .php : 70" [ref=e222] [cursor=pointer]':
          - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=70
          - generic [ref=e223]:
            - generic [ref=e224]: C:\projects\smart-african-marketplace\app\Http\Controllers\Public\ServiceController
            - generic [ref=e225]: .php
            - generic [ref=e226]: ": 70"
        - main [ref=e227]:
          - navigation [ref=e228]:
            - generic [ref=e229]:
              - paragraph [ref=e230]: "55"
              - paragraph [ref=e231]: "56"
              - paragraph [ref=e232]: "57"
              - paragraph [ref=e233]: "58"
              - paragraph [ref=e234]: "59"
              - paragraph [ref=e235]: "60"
              - paragraph [ref=e236]: "61"
              - paragraph [ref=e237]: "62"
              - paragraph [ref=e238]: "63"
              - paragraph [ref=e239]: "64"
              - paragraph [ref=e240]: "65"
              - paragraph [ref=e241]: "66"
              - paragraph [ref=e242]: "67"
              - paragraph [ref=e243]: "68"
              - paragraph [ref=e244]: "69"
              - paragraph [ref=e245]: "70"
              - paragraph [ref=e246]: "71"
              - paragraph [ref=e247]: "72"
              - paragraph [ref=e248]: "73"
              - paragraph [ref=e249]: "74"
              - paragraph [ref=e250]: "75"
              - paragraph [ref=e251]: "76"
              - paragraph [ref=e252]: "77"
              - paragraph [ref=e253]: "78"
              - paragraph [ref=e254]: "79"
              - paragraph [ref=e255]: "80"
              - paragraph [ref=e256]: "81"
              - paragraph [ref=e257]: "82"
              - paragraph [ref=e258]: "83"
              - paragraph [ref=e259]: "84"
          - code [ref=e262]:
            - generic [ref=e263]:
              - link [ref=e264] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=55
                - button [ref=e265]
              - generic [ref=e268]: $genderTargets = VendorServiceCategory::active()
            - generic [ref=e270]:
              - link [ref=e271] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=56
                - button [ref=e272]
              - generic [ref=e275]: "->whereNotNull('gender_target')"
            - generic [ref=e277]:
              - link [ref=e278] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=57
                - button [ref=e279]
              - generic [ref=e282]: "->distinct('gender_target')"
            - generic [ref=e284]:
              - link [ref=e285] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=58
                - button [ref=e286]
              - generic [ref=e289]: "->pluck('gender_target');"
            - link [ref=e292] [cursor=pointer]:
              - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=59
              - button [ref=e293]
            - generic [ref=e297]:
              - link [ref=e298] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=60
                - button [ref=e299]
              - generic [ref=e302]: return view('public.services.index', compact(
            - generic [ref=e304]:
              - link [ref=e305] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=61
                - button [ref=e306]
              - generic [ref=e309]: "'services',"
            - generic [ref=e311]:
              - link [ref=e312] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=62
                - button [ref=e313]
              - generic [ref=e316]: "'parentCategories',"
            - generic [ref=e318]:
              - link [ref=e319] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=63
                - button [ref=e320]
              - generic [ref=e323]: "'categoryTypes',"
            - generic [ref=e325]:
              - link [ref=e326] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=64
                - button [ref=e327]
              - generic [ref=e330]: "'genderTargets'"
            - generic [ref=e332]:
              - link [ref=e333] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=65
                - button [ref=e334]
              - generic [ref=e337]: ));
            - generic [ref=e338]:
              - link [ref=e339] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=66
                - button [ref=e340]
              - generic [ref=e343]: "}"
            - link [ref=e345] [cursor=pointer]:
              - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=67
              - button [ref=e346]
            - generic [ref=e350]:
              - link [ref=e351] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=68
                - button [ref=e352]
              - generic [ref=e355]: public function byCategory(Request $request, string $category)
            - generic [ref=e357]:
              - link [ref=e358] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=69
                - button [ref=e359]
              - generic [ref=e362]: "{"
            - generic [ref=e364]:
              - link [ref=e365] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=70
                - button [ref=e366]
              - generic [ref=e369]: $category = VendorServiceCategory::where('slug', $categorySlug)
            - generic [ref=e371]:
              - link [ref=e372] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=71
                - button [ref=e373]
              - generic [ref=e376]: "->where('is_active', true)"
            - generic [ref=e378]:
              - link [ref=e379] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=72
                - button [ref=e380]
              - generic [ref=e383]: "->with(['parent', 'children' => function ($q) {"
            - generic [ref=e385]:
              - link [ref=e386] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=73
                - button [ref=e387]
              - generic [ref=e390]: $q->active()->orderBy('sort_order')->orderBy('name');
            - generic [ref=e392]:
              - link [ref=e393] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=74
                - button [ref=e394]
              - generic [ref=e397]: "}])"
            - generic [ref=e398]:
              - link [ref=e399] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=75
                - button [ref=e400]
              - generic [ref=e403]: "->firstOrFail();"
            - link [ref=e405] [cursor=pointer]:
              - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=76
              - button [ref=e406]
            - generic [ref=e410]:
              - link [ref=e411] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=77
                - button [ref=e412]
              - generic [ref=e415]: // Build breadcrumb path
            - generic [ref=e417]:
              - link [ref=e418] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=78
                - button [ref=e419]
              - generic [ref=e422]: $breadcrumb = [$category];
            - generic [ref=e424]:
              - link [ref=e425] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=79
                - button [ref=e426]
              - generic [ref=e429]: $parent = $category->parent;
            - generic [ref=e431]:
              - link [ref=e432] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=80
                - button [ref=e433]
              - generic [ref=e436]: "while ($parent) {"
            - generic [ref=e438]:
              - link [ref=e439] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=81
                - button [ref=e440]
              - generic [ref=e443]: array_unshift($breadcrumb, $parent);
            - generic [ref=e445]:
              - link [ref=e446] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=82
                - button [ref=e447]
              - generic [ref=e450]: $parent = $parent->parent;
            - generic [ref=e452]:
              - link [ref=e453] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=83
                - button [ref=e454]
              - generic [ref=e457]: "}"
            - link [ref=e459] [cursor=pointer]:
              - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Capp%5CHttp%5CControllers%5CPublic%5CServiceController.php&line=84
              - button [ref=e460]
    - generic [ref=e465]:
      - navigation [ref=e466]:
        - list [ref=e468]:
          - listitem [ref=e469]:
            - link "App" [ref=e470] [cursor=pointer]:
              - /url: "#context-app"
            - list [ref=e471]:
              - listitem [ref=e472]:
                - link "Routing" [ref=e473] [cursor=pointer]:
                  - /url: "#context-app-routing"
          - listitem [ref=e478]:
            - link "Request" [ref=e479] [cursor=pointer]:
              - /url: "#context-request"
            - list [ref=e480]:
              - listitem [ref=e481]:
                - link "Browser" [ref=e482] [cursor=pointer]:
                  - /url: "#context-request-browser"
              - listitem [ref=e487]:
                - link "Headers" [ref=e488] [cursor=pointer]:
                  - /url: "#context-request-headers"
              - listitem [ref=e493]:
                - link "Body" [ref=e494] [cursor=pointer]:
                  - /url: "#context-request-body"
          - listitem [ref=e499]:
            - link "Context" [ref=e500] [cursor=pointer]:
              - /url: "#context-context"
            - list [ref=e501]:
              - listitem [ref=e502]:
                - link "Versions" [ref=e503] [cursor=pointer]:
                  - /url: "#context-context-versions"
      - generic [ref=e508]:
        - generic [ref=e509]:
          - heading "App" [level=2] [ref=e510]
          - generic [ref=e512]:
            - heading "Routing" [level=1] [ref=e513]
            - generic [ref=e517]:
              - generic [ref=e518]:
                - term [ref=e519]: Controller
                - definition [ref=e520]:
                  - generic [ref=e521]:
                    - code [ref=e524]: App\Http\Controllers\Public\ServiceController@byCategory
                    - button "Copy to clipboard" [ref=e526] [cursor=pointer]
              - generic [ref=e529]:
                - term [ref=e530]: Route name
                - definition [ref=e531]:
                  - generic [ref=e532]:
                    - code [ref=e535]: services.category
                    - button "Copy to clipboard" [ref=e537] [cursor=pointer]
              - generic [ref=e540]:
                - term [ref=e541]: Middleware
                - definition [ref=e542]:
                  - list [ref=e543]:
                    - listitem [ref=e544]:
                      - generic [ref=e545]:
                        - code [ref=e548]: web
                        - button "Copy to clipboard" [ref=e550] [cursor=pointer]
        - generic [ref=e553]:
          - heading "Request" [level=2] [ref=e554]
          - generic [ref=e555]:
            - generic [ref=e556]:
              - generic [ref=e557]:
                - generic [ref=e558]: http://localhost:8000/services/electrician-services
                - generic [ref=e559]: GET
              - generic [ref=e561] [cursor=pointer]:
                - code [ref=e564]:
                  - code [ref=e566]: "curl \"http://localhost:8000/services/electrician-services\" \\ -X GET \\ -H 'host: localhost:8000' \\ -H 'connection: keep-alive' \\ -H 'sec-ch-ua: \"Chromium\";v=\"158\", \"Not.A/Brand\";v=\"24\", \"HeadlessChrome\";v=\"158\"' \\ -H 'sec-ch-ua-mobile: ?0' \\ -H 'sec-ch-ua-platform: \"Windows\"' \\ -H 'upgrade-insecure-requests: 1' \\ -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36' \\ -H 'accept-language: en-US' \\ -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' \\ -H 'sec-fetch-site: none' \\ -H 'sec-fetch-mode: navigate' \\ -H 'sec-fetch-user: ?1' \\ -H 'sec-fetch-dest: document' \\ -H 'accept-encoding: gzip, deflate, br, zstd';"
                - button "Copy to clipboard" [ref=e568]
                - button [ref=e571]
            - generic [ref=e574]:
              - heading "Browser" [level=1] [ref=e575]
              - generic [ref=e579]:
                - code [ref=e582]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36
                - button "Copy to clipboard" [ref=e584] [cursor=pointer]
            - generic [ref=e587]:
              - heading "Headers" [level=1] [ref=e588]
              - generic [ref=e592]:
                - generic [ref=e593]:
                  - term [ref=e594]: host
                  - definition [ref=e595]:
                    - generic [ref=e596]:
                      - code [ref=e599]: localhost:8000
                      - button "Copy to clipboard" [ref=e601] [cursor=pointer]
                - generic [ref=e604]:
                  - term [ref=e605]: connection
                  - definition [ref=e606]:
                    - generic [ref=e607]:
                      - code [ref=e610]: keep-alive
                      - button "Copy to clipboard" [ref=e612] [cursor=pointer]
                - generic [ref=e615]:
                  - term [ref=e616]: sec-ch-ua
                  - definition [ref=e617]:
                    - generic [ref=e618]:
                      - code [ref=e621]: "\"Chromium\";v=\"158\", \"Not.A/Brand\";v=\"24\", \"HeadlessChrome\";v=\"158\""
                      - button "Copy to clipboard" [ref=e623] [cursor=pointer]
                - generic [ref=e626]:
                  - term [ref=e627]: sec-ch-ua-mobile
                  - definition [ref=e628]:
                    - generic [ref=e629]:
                      - code [ref=e632]: "?0"
                      - button "Copy to clipboard" [ref=e634] [cursor=pointer]
                - generic [ref=e637]:
                  - term [ref=e638]: sec-ch-ua-platform
                  - definition [ref=e639]:
                    - generic [ref=e640]:
                      - code [ref=e643]: "\"Windows\""
                      - button "Copy to clipboard" [ref=e645] [cursor=pointer]
                - generic [ref=e648]:
                  - term [ref=e649]: upgrade-insecure-requests
                  - definition [ref=e650]:
                    - generic [ref=e651]:
                      - code [ref=e654]: "1"
                      - button "Copy to clipboard" [ref=e656] [cursor=pointer]
                - generic [ref=e659]:
                  - term [ref=e660]: user-agent
                  - definition [ref=e661]:
                    - generic [ref=e662]:
                      - code [ref=e665]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36
                      - button "Copy to clipboard" [ref=e667] [cursor=pointer]
                - generic [ref=e670]:
                  - term [ref=e671]: accept-language
                  - definition [ref=e672]:
                    - generic [ref=e673]:
                      - code [ref=e676]: en-US
                      - button "Copy to clipboard" [ref=e678] [cursor=pointer]
                - generic [ref=e681]:
                  - term [ref=e682]: accept
                  - definition [ref=e683]:
                    - generic [ref=e684]:
                      - code [ref=e687]: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
                      - button "Copy to clipboard" [ref=e689] [cursor=pointer]
                - generic [ref=e692]:
                  - term [ref=e693]: sec-fetch-site
                  - definition [ref=e694]:
                    - generic [ref=e695]:
                      - code [ref=e698]: none
                      - button "Copy to clipboard" [ref=e700] [cursor=pointer]
                - generic [ref=e703]:
                  - term [ref=e704]: sec-fetch-mode
                  - definition [ref=e705]:
                    - generic [ref=e706]:
                      - code [ref=e709]: navigate
                      - button "Copy to clipboard" [ref=e711] [cursor=pointer]
                - generic [ref=e714]:
                  - term [ref=e715]: sec-fetch-user
                  - definition [ref=e716]:
                    - generic [ref=e717]:
                      - code [ref=e720]: "?1"
                      - button "Copy to clipboard" [ref=e722] [cursor=pointer]
                - generic [ref=e725]:
                  - term [ref=e726]: sec-fetch-dest
                  - definition [ref=e727]:
                    - generic [ref=e728]:
                      - code [ref=e731]: document
                      - button "Copy to clipboard" [ref=e733] [cursor=pointer]
                - generic [ref=e736]:
                  - term [ref=e737]: accept-encoding
                  - definition [ref=e738]:
                    - generic [ref=e739]:
                      - code [ref=e742]: gzip, deflate, br, zstd
                      - button "Copy to clipboard" [ref=e744] [cursor=pointer]
            - generic [ref=e747]:
              - heading "Body" [level=1] [ref=e748]
              - generic [ref=e752]:
                - code [ref=e755]: "[]"
                - button "Copy to clipboard" [ref=e757] [cursor=pointer]
        - generic [ref=e760]:
          - heading "Context" [level=2] [ref=e761]
          - generic [ref=e763]:
            - heading "Versions" [level=1] [ref=e764]
            - generic [ref=e768]:
              - generic [ref=e769]:
                - term [ref=e770]: Php Version
                - definition [ref=e771]:
                  - generic [ref=e772]:
                    - code [ref=e775]: 8.5.10
                    - button "Copy to clipboard" [ref=e777] [cursor=pointer]
              - generic [ref=e780]:
                - term [ref=e781]: Laravel Version
                - definition [ref=e782]:
                  - generic [ref=e783]:
                    - code [ref=e786]: 12.71.0
                    - button "Copy to clipboard" [ref=e788] [cursor=pointer]
              - generic [ref=e791]:
                - term [ref=e792]: Laravel Locale
                - definition [ref=e793]:
                  - generic [ref=e794]:
                    - code [ref=e797]: en
                    - button "Copy to clipboard" [ref=e799] [cursor=pointer]
              - generic [ref=e802]:
                - term [ref=e803]: Laravel Config Cached
                - definition [ref=e804]:
                  - generic [ref=e805]: "false"
              - generic [ref=e809]:
                - term [ref=e810]: App Debug
                - definition [ref=e811]:
                  - generic [ref=e812]: "true"
              - generic [ref=e816]:
                - term [ref=e817]: App Env
                - definition [ref=e818]:
                  - generic [ref=e819]:
                    - code [ref=e822]: local
                    - button "Copy to clipboard" [ref=e824] [cursor=pointer]
    - generic [ref=e828]:
      - navigation:
        - list [ref=e829]:
          - listitem [ref=e830]:
            - button "6 Queries" [ref=e831] [cursor=pointer]:
              - generic [ref=e832]: "6"
              - generic [ref=e833]: Queries
      - generic [ref=e834]:
        - generic [ref=e835]:
          - generic [ref=e836]:
            - generic [ref=e837]: 9:56:25 AM
            - generic [ref=e838]:
              - img "Runtime" [ref=e839]
              - text: 2.56ms
            - generic [ref=e841]:
              - img "Connection" [ref=e842]
              - text: mysql
          - generic [ref=e845] [cursor=pointer]:
            - code [ref=e848]:
              - code [ref=e850]: "select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'settings' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`"
            - button "Copy to clipboard" [ref=e852]
            - button [ref=e855]
        - generic [ref=e858]:
          - generic [ref=e859]:
            - generic [ref=e860]: 9:56:25 AM
            - generic [ref=e861]:
              - img "Runtime" [ref=e862]
              - text: 0.66ms
            - generic [ref=e864]:
              - img "Connection" [ref=e865]
              - text: mysql
          - generic [ref=e868] [cursor=pointer]:
            - code [ref=e871]:
              - code [ref=e873]: "select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'cache' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`"
            - button "Copy to clipboard" [ref=e875]
            - button [ref=e878]
        - generic [ref=e881]:
          - generic [ref=e882]:
            - generic [ref=e883]: 9:56:25 AM
            - generic [ref=e884]:
              - img "Runtime" [ref=e885]
              - text: 0.6ms
            - generic [ref=e887]:
              - img "Connection" [ref=e888]
              - text: mysql
          - generic [ref=e891]:
            - generic [ref=e892] [cursor=pointer]:
              - code [ref=e895]:
                - code [ref=e897]: "select * from `cache` where `key` in (smart_african_market_cache_mail.configured_from_db)"
              - button "Copy to clipboard" [ref=e899]
              - button [ref=e902]
            - button "1 query parameter" [ref=e906] [cursor=pointer]
        - generic [ref=e909]:
          - generic [ref=e910]:
            - generic [ref=e911]: 9:56:25 AM
            - generic [ref=e912]:
              - img "Runtime" [ref=e913]
              - text: 0.66ms
            - generic [ref=e915]:
              - img "Connection" [ref=e916]
              - text: mysql
          - generic [ref=e919]:
            - generic [ref=e920] [cursor=pointer]:
              - code [ref=e923]:
                - code [ref=e925]: "select * from `settings` where `key` = mail_host limit 1"
              - button "Copy to clipboard" [ref=e927]
              - button [ref=e930]
            - button "1 query parameter" [ref=e934] [cursor=pointer]
        - generic [ref=e937]:
          - generic [ref=e938]:
            - generic [ref=e939]: 9:56:25 AM
            - generic [ref=e940]:
              - img "Runtime" [ref=e941]
              - text: 1.3ms
            - generic [ref=e943]:
              - img "Connection" [ref=e944]
              - text: mysql
          - generic [ref=e947]:
            - generic [ref=e948] [cursor=pointer]:
              - code [ref=e951]:
                - code [ref=e953]: "select * from `sessions` where `id` = GDXLkdXzRbjZTT9fkJ3ddryNuK3fyv1ABxQ6HXRe limit 1"
              - button "Copy to clipboard" [ref=e955]
              - button [ref=e958]
            - button "1 query parameter" [ref=e962] [cursor=pointer]
        - generic [ref=e965]:
          - generic [ref=e966]:
            - generic [ref=e967]: 9:56:25 AM
            - generic [ref=e968]:
              - img "Runtime" [ref=e969]
              - text: 0.9ms
            - generic [ref=e971]:
              - img "Connection" [ref=e972]
              - text: mysql
          - generic [ref=e975]:
            - generic [ref=e976] [cursor=pointer]:
              - code [ref=e979]:
                - code [ref=e981]: "select * from `cache` where `key` in (smart_african_market_cache_maintenance_mode)"
              - button "Copy to clipboard" [ref=e983]
              - button [ref=e986]
            - button "1 query parameter" [ref=e990] [cursor=pointer]
    - generic [ref=e994]:
      - list [ref=e995]:
        - listitem [ref=e996]
        - listitem [ref=e1005]: ·
        - listitem [ref=e1006]:
          - link "Source" [ref=e1007] [cursor=pointer]:
            - /url: https://github.com/spatie/laravel-ignition
        - listitem [ref=e1008]: ·
        - listitem [ref=e1009]:
          - link "Docs" [ref=e1010] [cursor=pointer]:
            - /url: https://flareapp.io/docs/ignition/introducing-ignition/overview
        - listitem [ref=e1011]: ·
        - listitem [ref=e1012]:
          - link "Laravel" [ref=e1013] [cursor=pointer]:
            - /url: https://laravel.com
      - paragraph [ref=e1015]:
        - text: Ignition is built by
        - link "Flare" [ref=e1016] [cursor=pointer]:
          - /url: https://flareapp.io/?utm_campaign=ignition&utm_source=ignition
        - text: ", the Laravel error reporting service."
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | test.describe('Service Category Pages', () => {
  4   |   // All parent category slugs
  5   |   const parentCategories = [
  6   |     'beauty-grooming',
  7   |     'automotive',
  8   |     'home-services',
  9   |     'commercial-services',
  10  |   ];
  11  | 
  12  |   // All sub-category slugs
  13  |   const subCategories = [
  14  |     'mens-salon-services',
  15  |     'womens-salon-services',
  16  |     'car-washing-bay',
  17  |     'car-garage',
  18  |     'mechanic-services',
  19  |     'painter-services',
  20  |     'electrician-services',
  21  |     'home-assistance-services',
  22  |     'facility-management-services',
  23  |     'construction-services',
  24  |   ];
  25  | 
  26  |   // All leaf category slugs
  27  |   const leafCategories = [
  28  |     'mens-haircut',
  29  |     'mens-shave-beard-trim',
  30  |     'mens-grooming-package',
  31  |     'womens-haircut',
  32  |     'womens-hair-coloring',
  33  |     'womens-hair-treatment',
  34  |     'womens-makeup',
  35  |     'womens-nail-services',
  36  |     'exterior-car-wash',
  37  |     'interior-car-detailing',
  38  |     'car-wax-polish',
  39  |     'full-car-detailing',
  40  |     'oil-change-service',
  41  |     'brake-service',
  42  |     'ac-service-repair',
  43  |     'engine-diagnostics',
  44  |     'general-vehicle-maintenance',
  45  |     'fleet-maintenance',
  46  |     'interior-painting',
  47  |     'exterior-painting',
  48  |     'home-electrical-wiring',
  49  |     'commercial-electrical',
  50  |     'home-cleaning',
  51  |     'caregiving-services',
  52  |     'home-cooking',
  53  |     'building-cleaning',
  54  |     'facility-maintenance',
  55  |     'residential-construction',
  56  |     'commercial-construction',
  57  |   ];
  58  | 
  59  |   test('main services page loads without errors', async ({ page }) => {
  60  |     const response = await page.goto('/services');
  61  |     expect(response.status()).toBe(200);
  62  |     await expect(page).toHaveTitle(/Services/);
  63  |   });
  64  | 
  65  |   test.describe('Parent category pages', () => {
  66  |     for (const slug of parentCategories) {
  67  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  68  |         const response = await page.goto(`/services/${slug}`);
  69  |         expect(response.status()).toBe(200);
  70  |         await expect(page).toHaveTitle(/Services/);
  71  |       });
  72  |     }
  73  |   });
  74  | 
  75  |   test.describe('Sub-category pages', () => {
  76  |     for (const slug of subCategories) {
  77  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  78  |         const response = await page.goto(`/services/${slug}`);
> 79  |         expect(response.status()).toBe(200);
      |                                   ^ Error: expect(received).toBe(expected) // Object.is equality
  80  |         await expect(page).toHaveTitle(/Services/);
  81  |       });
  82  |     }
  83  |   });
  84  | 
  85  |   test.describe('Leaf category pages', () => {
  86  |     for (const slug of leafCategories) {
  87  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  88  |         const response = await page.goto(`/services/${slug}`);
  89  |         expect(response.status()).toBe(200);
  90  |         await expect(page).toHaveTitle(/Services/);
  91  |       });
  92  |     }
  93  |   });
  94  | 
  95  |   test('leaf category pages show services when available', async ({ page }) => {
  96  |     // Visit a leaf category page that should have services
  97  |     const response = await page.goto('/services/mens-haircut');
  98  |     expect(response.status()).toBe(200);
  99  |     
  100 |     // Check for either services listed or a "no services" message
  101 |     const hasServiceCards = await page.locator('.service-card').count() > 0;
  102 |     const hasNoServicesMessage = await page.locator('text=No services found').count() > 0;
  103 |     const hasServicesHeading = await page.locator('h1').count() > 0;
  104 |     
  105 |     expect(hasServiceCards || hasNoServicesMessage || hasServicesHeading).toBe(true);
  106 |   });
  107 | 
  108 |   test('category page has proper heading hierarchy', async ({ page }) => {
  109 |     await page.goto('/services/beauty-grooming');
  110 |     await expect(page.locator('h1')).toHaveCount(1);
  111 |   });
  112 | 
  113 |   test('category page links are functional', async ({ page }) => {
  114 |     await page.goto('/services/beauty-grooming');
  115 |     
  116 |     // Click on a sub-category link
  117 |     const menSalonLink = page.locator('a[href="/services/mens-salon-services"]').first();
  118 |     if (await menSalonLink.count() > 0) {
  119 |       await menSalonLink.click();
  120 |       await expect(page).toHaveURL(/.*\/services\/mens-salon-services/);
  121 |     }
  122 |   });
  123 | });
  124 | 
```