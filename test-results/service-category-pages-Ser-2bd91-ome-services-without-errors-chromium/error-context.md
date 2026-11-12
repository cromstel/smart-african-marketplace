# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: service-category-pages.spec.ts >> Service Category Pages >> Parent category pages >> loads /services/home-services without errors
- Location: tests\e2e\service-category-pages.spec.ts:67:7

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
      - link "Unclosed '[' on line 134 does not match ')'" [ref=e56] [cursor=pointer]:
        - /url: "#top"
  - main [ref=e58]:
    - main [ref=e62]:
      - generic [ref=e64]:
        - generic [ref=e65]:
          - generic [ref=e66]: ParseError
          - generic [ref=e69]:
            - generic [ref=e70]: PHP 8.5.10
            - generic [ref=e71]: 12.71.0
        - generic [ref=e74]: Unclosed '[' on line 134 does not match ')'
    - generic [ref=e78]:
      - complementary [ref=e79]:
        - generic [ref=e80]:
          - button "Expand vendor frames" [ref=e82] [cursor=pointer]
          - list [ref=e89]:
            - listitem [ref=e90]:
              - generic [ref=e92]:
                - generic [ref=e93]: Illuminate
                - generic [ref=e94]: \
                - generic [ref=e95]: Filesystem
                - generic [ref=e96]: \
                - generic [ref=e97]: Filesystem
                - generic [ref=e98]: ": 118"
              - generic [ref=e99]: "{closure:Illuminate\\Filesystem\\Filesystem::getRequire():120}"
            - listitem [ref=e100]
            - listitem [ref=e101] [cursor=pointer]: 14 vendor frames
            - listitem [ref=e104] [cursor=pointer]:
              - generic [ref=e106]:
                - generic [ref=e107]: App
                - generic [ref=e108]: \
                - generic [ref=e109]: Http
                - generic [ref=e110]: \
                - generic [ref=e111]: Middleware
                - generic [ref=e112]: \
                - generic [ref=e113]: MaintenanceMode
                - generic [ref=e114]: ": 54"
              - generic [ref=e115]: handle
            - listitem [ref=e116] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e119] [cursor=pointer]:
              - generic [ref=e121]:
                - generic [ref=e122]: App
                - generic [ref=e123]: \
                - generic [ref=e124]: Http
                - generic [ref=e125]: \
                - generic [ref=e126]: Middleware
                - generic [ref=e127]: \
                - generic [ref=e128]: CompressResponse
                - generic [ref=e129]: ": 13"
              - generic [ref=e130]: handle
            - listitem [ref=e131] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e134] [cursor=pointer]:
              - generic [ref=e136]:
                - generic [ref=e137]: App
                - generic [ref=e138]: \
                - generic [ref=e139]: Http
                - generic [ref=e140]: \
                - generic [ref=e141]: Middleware
                - generic [ref=e142]: \
                - generic [ref=e143]: TrackUserBehavior
                - generic [ref=e144]: ": 14"
              - generic [ref=e145]: handle
            - listitem [ref=e146] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e149] [cursor=pointer]:
              - generic [ref=e151]:
                - generic [ref=e152]: App
                - generic [ref=e153]: \
                - generic [ref=e154]: Http
                - generic [ref=e155]: \
                - generic [ref=e156]: Middleware
                - generic [ref=e157]: \
                - generic [ref=e158]: CaptureCampaignContext
                - generic [ref=e159]: ": 31"
              - generic [ref=e160]: handle
            - listitem [ref=e161] [cursor=pointer]: 1 vendor frame
            - listitem [ref=e164] [cursor=pointer]:
              - generic [ref=e166]:
                - generic [ref=e167]: App
                - generic [ref=e168]: \
                - generic [ref=e169]: Http
                - generic [ref=e170]: \
                - generic [ref=e171]: Middleware
                - generic [ref=e172]: \
                - generic [ref=e173]: SetLocale
                - generic [ref=e174]: ": 22"
              - generic [ref=e175]: handle
            - listitem [ref=e176] [cursor=pointer]: 14 vendor frames
            - listitem [ref=e179] [cursor=pointer]:
              - generic [ref=e181]:
                - generic [ref=e182]: App
                - generic [ref=e183]: \
                - generic [ref=e184]: Http
                - generic [ref=e185]: \
                - generic [ref=e186]: Middleware
                - generic [ref=e187]: \
                - generic [ref=e188]: TraceMiddleware
                - generic [ref=e189]: ": 24"
              - generic [ref=e190]: handle
            - listitem [ref=e191] [cursor=pointer]: 10 vendor frames
            - listitem [ref=e194] [cursor=pointer]:
              - generic [ref=e196]:
                - generic [ref=e197]: App
                - generic [ref=e198]: \
                - generic [ref=e199]: Http
                - generic [ref=e200]: \
                - generic [ref=e201]: Middleware
                - generic [ref=e202]: \
                - generic [ref=e203]: PreventCrlfInjection
                - generic [ref=e204]: ": 79"
              - generic [ref=e205]: handle
            - listitem [ref=e206] [cursor=pointer]: 18 vendor frames
            - listitem [ref=e209] [cursor=pointer]:
              - generic [ref=e211]:
                - generic [ref=e212]: C:\projects\smart-african-marketplace\public\index
                - generic [ref=e213]: .php
                - generic [ref=e214]: ": 58"
              - generic [ref=e215]: "[top]"
      - generic [ref=e216]:
        - 'link "C:\\projects\\smart-african-marketplace\\resources\\views\\public\\services\\category.blade .php : 118" [ref=e218] [cursor=pointer]':
          - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=118
          - generic [ref=e219]:
            - generic [ref=e220]: C:\projects\smart-african-marketplace\resources\views\public\services\category.blade
            - generic [ref=e221]: .php
            - generic [ref=e222]: ": 118"
        - main [ref=e223]:
          - navigation [ref=e224]:
            - generic [ref=e225]:
              - paragraph [ref=e226]: "103"
              - paragraph [ref=e227]: "104"
              - paragraph [ref=e228]: "105"
              - paragraph [ref=e229]: "106"
              - paragraph [ref=e230]: "107"
              - paragraph [ref=e231]: "108"
              - paragraph [ref=e232]: "109"
              - paragraph [ref=e233]: "110"
              - paragraph [ref=e234]: "111"
              - paragraph [ref=e235]: "112"
              - paragraph [ref=e236]: "113"
              - paragraph [ref=e237]: "114"
              - paragraph [ref=e238]: "115"
              - paragraph [ref=e239]: "116"
              - paragraph [ref=e240]: "117"
              - paragraph [ref=e241]: "118"
              - paragraph [ref=e242]: "119"
              - paragraph [ref=e243]: "120"
              - paragraph [ref=e244]: "121"
              - paragraph [ref=e245]: "122"
              - paragraph [ref=e246]: "123"
              - paragraph [ref=e247]: "124"
              - paragraph [ref=e248]: "125"
              - paragraph [ref=e249]: "126"
              - paragraph [ref=e250]: "127"
              - paragraph [ref=e251]: "128"
              - paragraph [ref=e252]: "129"
              - paragraph [ref=e253]: "130"
              - paragraph [ref=e254]: "131"
              - paragraph [ref=e255]: "132"
          - code [ref=e258]:
            - generic [ref=e259]:
              - link [ref=e260] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=103
                - button [ref=e261]
              - generic [ref=e264]: </svg>
            - generic [ref=e266]:
              - link [ref=e267] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=104
                - button [ref=e268]
              - generic [ref=e271]: </div>
            - generic [ref=e273]:
              - link [ref=e274] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=105
                - button [ref=e275]
              - generic [ref=e278]: <h3 class="text-xl font-semibold text-white mb-2">No services found</h3>
            - generic [ref=e280]:
              - link [ref=e281] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=106
                - button [ref=e282]
              - generic [ref=e285]: <p class="text-gray-400 max-w-md mx-auto">
            - generic [ref=e287]:
              - link [ref=e288] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=107
                - button [ref=e289]
              - generic [ref=e292]: No services available in this category yet. Check back soon!
            - generic [ref=e293]:
              - link [ref=e294] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=108
                - button [ref=e295]
              - generic [ref=e298]: </p>
            - generic [ref=e300]:
              - link [ref=e301] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=109
                - button [ref=e302]
              - generic [ref=e305]: </div>
            - generic [ref=e307]:
              - link [ref=e308] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=110
                - button [ref=e309]
              - generic [ref=e312]: "@endforelse"
            - generic [ref=e314]:
              - link [ref=e315] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=111
                - button [ref=e316]
              - generic [ref=e319]: </div>
            - link [ref=e322] [cursor=pointer]:
              - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=112
              - button [ref=e323]
            - generic [ref=e327]:
              - link [ref=e328] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=113
                - button [ref=e329]
              - generic [ref=e332]: "{{ $services->links() }}"
            - generic [ref=e334]:
              - link [ref=e335] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=114
                - button [ref=e336]
              - generic [ref=e339]: </div>
            - link [ref=e342] [cursor=pointer]:
              - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=115
              - button [ref=e343]
            - generic [ref=e347]:
              - link [ref=e348] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=116
                - button [ref=e349]
              - generic [ref=e352]: <!-- Sidebar -->
            - generic [ref=e354]:
              - link [ref=e355] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=117
                - button [ref=e356]
              - generic [ref=e359]: <aside class="lg:sticky lg:top-24 order-first lg:order-first lg:w-[300px]">
            - generic [ref=e361]:
              - link [ref=e362] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=118
                - button [ref=e363]
              - generic [ref=e366]: "@include('public.services._sidebar', ["
            - generic [ref=e368]:
              - link [ref=e369] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=119
                - button [ref=e370]
              - generic [ref=e373]: "'parentCategories' => $parentCategories,"
            - generic [ref=e374]:
              - link [ref=e375] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=120
                - button [ref=e376]
              - generic [ref=e379]: "'currentCategorySlug' => $category->slug,"
            - generic [ref=e380]:
              - link [ref=e381] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=121
                - button [ref=e382]
              - generic [ref=e385]: "'currentRouteName' => request()->route()->getName(),"
            - generic [ref=e386]:
              - link [ref=e387] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=122
                - button [ref=e388]
              - generic [ref=e391]: "'filterParams' => ["
            - generic [ref=e392]:
              - link [ref=e393] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=123
                - button [ref=e394]
              - generic [ref=e397]: "'search' => request('search'),"
            - generic [ref=e398]:
              - link [ref=e399] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=124
                - button [ref=e400]
              - generic [ref=e403]: "'category_type' => request('category_type'),"
            - generic [ref=e404]:
              - link [ref=e405] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=125
                - button [ref=e406]
              - generic [ref=e409]: "'gender_target' => request('gender_target'),"
            - generic [ref=e410]:
              - link [ref=e411] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=126
                - button [ref=e412]
              - generic [ref=e415]: "'categoryTypes' => $parentCategories,"
            - generic [ref=e416]:
              - link [ref=e417] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=127
                - button [ref=e418]
              - generic [ref=e421]: "'genderTargets' => VendorServiceCategory::active()"
            - generic [ref=e422]:
              - link [ref=e423] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=128
                - button [ref=e424]
              - generic [ref=e427]: "->whereNotNull('gender_target')"
            - generic [ref=e428]:
              - link [ref=e429] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=129
                - button [ref=e430]
              - generic [ref=e433]: "->distinct('gender_target')"
            - generic [ref=e434]:
              - link [ref=e435] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=130
                - button [ref=e436]
              - generic [ref=e439]: "->pluck('gender_target')"
            - generic [ref=e440]:
              - link [ref=e441] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=131
                - button [ref=e442]
              - generic [ref=e445]: "])"
            - generic [ref=e446]:
              - link [ref=e447] [cursor=pointer]:
                - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=132
                - button [ref=e448]
              - generic [ref=e451]: </aside>
    - generic [ref=e454]:
      - navigation [ref=e455]:
        - list [ref=e457]:
          - listitem [ref=e458]:
            - link "App" [ref=e459] [cursor=pointer]:
              - /url: "#context-app"
            - list [ref=e460]:
              - listitem [ref=e461]:
                - link "Routing" [ref=e462] [cursor=pointer]:
                  - /url: "#context-app-routing"
              - listitem [ref=e467]:
                - link "Views" [ref=e468] [cursor=pointer]:
                  - /url: "#context-app-views"
          - listitem [ref=e473]:
            - link "Request" [ref=e474] [cursor=pointer]:
              - /url: "#context-request"
            - list [ref=e475]:
              - listitem [ref=e476]:
                - link "Browser" [ref=e477] [cursor=pointer]:
                  - /url: "#context-request-browser"
              - listitem [ref=e482]:
                - link "Headers" [ref=e483] [cursor=pointer]:
                  - /url: "#context-request-headers"
              - listitem [ref=e488]:
                - link "Body" [ref=e489] [cursor=pointer]:
                  - /url: "#context-request-body"
          - listitem [ref=e494]:
            - link "Context" [ref=e495] [cursor=pointer]:
              - /url: "#context-context"
            - list [ref=e496]:
              - listitem [ref=e497]:
                - link "Versions" [ref=e498] [cursor=pointer]:
                  - /url: "#context-context-versions"
      - generic [ref=e503]:
        - generic [ref=e504]:
          - heading "App" [level=2] [ref=e505]
          - generic [ref=e506]:
            - generic [ref=e507]:
              - heading "Routing" [level=1] [ref=e508]
              - generic [ref=e512]:
                - generic [ref=e513]:
                  - term [ref=e514]: Controller
                  - definition [ref=e515]:
                    - generic [ref=e516]:
                      - code [ref=e519]: App\Http\Controllers\Public\ServiceController@byCategory
                      - button "Copy to clipboard" [ref=e521] [cursor=pointer]
                - generic [ref=e524]:
                  - term [ref=e525]: Route name
                  - definition [ref=e526]:
                    - generic [ref=e527]:
                      - code [ref=e530]: services.category
                      - button "Copy to clipboard" [ref=e532] [cursor=pointer]
                - generic [ref=e535]:
                  - term [ref=e536]: Middleware
                  - definition [ref=e537]:
                    - list [ref=e538]:
                      - listitem [ref=e539]:
                        - generic [ref=e540]:
                          - code [ref=e543]: web
                          - button "Copy to clipboard" [ref=e545] [cursor=pointer]
            - generic [ref=e548]:
              - heading "Views" [level=1] [ref=e549]
              - generic [ref=e553]:
                - generic [ref=e554]:
                  - term [ref=e555]: View
                  - definition [ref=e556]:
                    - link "C:\\projects\\smart-african-marketplace\\resources\\views\\public\\services\\category.blade .php" [ref=e557] [cursor=pointer]:
                      - /url: phpstorm://open?file=C%3A%5Cprojects%5Csmart-african-marketplace%5Cresources%5Cviews%5Cpublic%5Cservices%5Ccategory.blade.php&line=1
                      - generic [ref=e558]:
                        - generic [ref=e559]: C:\projects\smart-african-marketplace\resources\views\public\services\category.blade
                        - generic [ref=e560]: .php
                - generic [ref=e561]:
                  - term [ref=e562]: Data
                  - definition [ref=e563]:
                    - generic [ref=e564]:
                      - generic [ref=e565]:
                        - term [ref=e566]: errors
                        - definition [ref=e567]:
                          - generic [ref=e569]:
                            - text: "Illuminate\\Support\\ViewErrorBag {"
                            - generic "[Ctrl+click] Expand all children" [ref=e570] [cursor=pointer]:
                              - text: "#2396"
                              - generic [ref=e571]: ▼
                            - generic [ref=e572]: "#bags: []"
                            - text: "}"
                      - generic [ref=e573]:
                        - term [ref=e574]: category
                        - definition [ref=e575]:
                          - generic [ref=e577]:
                            - text: "App\\Models\\VendorServiceCategory {"
                            - generic "[Ctrl+click] Expand all children" [ref=e578] [cursor=pointer]:
                              - text: "#2839"
                              - generic [ref=e579]: ▼
                            - generic [ref=e580]:
                              - text: "#connection: \"mysql\" #table: \"vendor_service_categories\" #primaryKey: \"id\" #keyType: \"int\" +incrementing: true #with: [] #withCount: [] +preventsLazyLoading: false #perPage: 15 +exists: true +wasRecentlyCreated: false #escapeWhenCastingToString: false #attributes: array:12 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e581] [cursor=pointer]: ▶
                              - text: "] #original: array:12 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e583] [cursor=pointer]: ▶
                              - text: "] #changes: [] #previous: [] #casts: array:2 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e585] [cursor=pointer]: ▶
                              - text: "] #classCastCache: [] #attributeCastCache: [] #dateFormat: null #appends: [] #dispatchesEvents: [] #observables: [] #relations: array:2 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e587] [cursor=pointer]: ▶
                              - text: "] #touches: [] #relationAutoloadCallback: null #relationAutoloadContext: null +timestamps: true +usesUniqueIds: false #hidden: [] #visible: [] #fillable: array:9 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e589] [cursor=pointer]: ▶
                              - text: "] #guarded: array:1 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e591] [cursor=pointer]: ▶
                              - text: "]"
                            - text: "}"
                      - generic [ref=e593]:
                        - term [ref=e594]: breadcrumb
                        - definition [ref=e595]:
                          - generic [ref=e597]:
                            - text: array:1 [
                            - generic "[Ctrl+click] Expand all children" [ref=e598] [cursor=pointer]: ▼
                            - generic [ref=e600]:
                              - text: 0 =>
                              - generic "App\\Models\\VendorServiceCategory" [ref=e601]:
                                - generic [ref=e602]: App\Models
                                - generic [ref=e603]: \
                                - text: VendorServiceCategory
                              - text: "{"
                              - generic "[Ctrl+click] Expand all children" [ref=e604] [cursor=pointer]:
                                - text: "#2839"
                                - generic [ref=e605]: ▶
                              - text: "}"
                            - text: "]"
                      - generic [ref=e606]:
                        - term [ref=e607]: services
                        - definition [ref=e608]:
                          - generic [ref=e610]:
                            - text: "Illuminate\\Pagination\\LengthAwarePaginator {"
                            - generic "[Ctrl+click] Expand all children" [ref=e611] [cursor=pointer]:
                              - text: "#2413"
                              - generic [ref=e612]: ▼
                            - generic [ref=e613]:
                              - text: "#items:"
                              - generic "Illuminate\\Database\\Eloquent\\Collection" [ref=e614]:
                                - generic [ref=e615]: Illuminate\Database\Eloquent
                                - generic [ref=e616]: \
                                - text: Collection
                              - text: "{"
                              - generic "[Ctrl+click] Expand all children" [ref=e617] [cursor=pointer]:
                                - text: "#2410"
                                - generic [ref=e618]: ▶
                              - text: "} #perPage: 12 #currentPage: 1 #path: \"http://localhost:8000/services/home-services\" #query: [] #fragment: null #pageName: \"page\" #escapeWhenCastingToString: false +onEachSide: 3 #options: array:2 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e619] [cursor=pointer]: ▶
                              - text: "] #total: 0 #lastPage: 1"
                            - text: "}"
                      - generic [ref=e621]:
                        - term [ref=e622]: parentCategories
                        - definition [ref=e623]:
                          - generic [ref=e625]:
                            - text: "Illuminate\\Database\\Eloquent\\Collection {"
                            - generic "[Ctrl+click] Expand all children" [ref=e626] [cursor=pointer]:
                              - text: "#2415"
                              - generic [ref=e627]: ▼
                            - generic [ref=e628]:
                              - text: "#items: array:4 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e629] [cursor=pointer]: ▶
                              - text: "] #escapeWhenCastingToString: false"
                            - text: "}"
        - generic [ref=e631]:
          - heading "Request" [level=2] [ref=e632]
          - generic [ref=e633]:
            - generic [ref=e634]:
              - generic [ref=e635]:
                - generic [ref=e636]: http://localhost:8000/services/home-services
                - generic [ref=e637]: GET
              - generic [ref=e639] [cursor=pointer]:
                - code [ref=e642]:
                  - code [ref=e644]: "curl \"http://localhost:8000/services/home-services\" \\ -X GET \\ -H 'host: localhost:8000' \\ -H 'connection: keep-alive' \\ -H 'sec-ch-ua: \"Chromium\";v=\"158\", \"Not.A/Brand\";v=\"24\", \"HeadlessChrome\";v=\"158\"' \\ -H 'sec-ch-ua-mobile: ?0' \\ -H 'sec-ch-ua-platform: \"Windows\"' \\ -H 'upgrade-insecure-requests: 1' \\ -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36' \\ -H 'accept-language: en-US' \\ -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' \\ -H 'sec-fetch-site: none' \\ -H 'sec-fetch-mode: navigate' \\ -H 'sec-fetch-user: ?1' \\ -H 'sec-fetch-dest: document' \\ -H 'accept-encoding: gzip, deflate, br, zstd';"
                - button "Copy to clipboard" [ref=e646]
                - button [ref=e649]
            - generic [ref=e652]:
              - heading "Browser" [level=1] [ref=e653]
              - generic [ref=e657]:
                - code [ref=e660]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36
                - button "Copy to clipboard" [ref=e662] [cursor=pointer]
            - generic [ref=e665]:
              - heading "Headers" [level=1] [ref=e666]
              - generic [ref=e670]:
                - generic [ref=e671]:
                  - term [ref=e672]: host
                  - definition [ref=e673]:
                    - generic [ref=e674]:
                      - code [ref=e677]: localhost:8000
                      - button "Copy to clipboard" [ref=e679] [cursor=pointer]
                - generic [ref=e682]:
                  - term [ref=e683]: connection
                  - definition [ref=e684]:
                    - generic [ref=e685]:
                      - code [ref=e688]: keep-alive
                      - button "Copy to clipboard" [ref=e690] [cursor=pointer]
                - generic [ref=e693]:
                  - term [ref=e694]: sec-ch-ua
                  - definition [ref=e695]:
                    - generic [ref=e696]:
                      - code [ref=e699]: "\"Chromium\";v=\"158\", \"Not.A/Brand\";v=\"24\", \"HeadlessChrome\";v=\"158\""
                      - button "Copy to clipboard" [ref=e701] [cursor=pointer]
                - generic [ref=e704]:
                  - term [ref=e705]: sec-ch-ua-mobile
                  - definition [ref=e706]:
                    - generic [ref=e707]:
                      - code [ref=e710]: "?0"
                      - button "Copy to clipboard" [ref=e712] [cursor=pointer]
                - generic [ref=e715]:
                  - term [ref=e716]: sec-ch-ua-platform
                  - definition [ref=e717]:
                    - generic [ref=e718]:
                      - code [ref=e721]: "\"Windows\""
                      - button "Copy to clipboard" [ref=e723] [cursor=pointer]
                - generic [ref=e726]:
                  - term [ref=e727]: upgrade-insecure-requests
                  - definition [ref=e728]:
                    - generic [ref=e729]:
                      - code [ref=e732]: "1"
                      - button "Copy to clipboard" [ref=e734] [cursor=pointer]
                - generic [ref=e737]:
                  - term [ref=e738]: user-agent
                  - definition [ref=e739]:
                    - generic [ref=e740]:
                      - code [ref=e743]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36
                      - button "Copy to clipboard" [ref=e745] [cursor=pointer]
                - generic [ref=e748]:
                  - term [ref=e749]: accept-language
                  - definition [ref=e750]:
                    - generic [ref=e751]:
                      - code [ref=e754]: en-US
                      - button "Copy to clipboard" [ref=e756] [cursor=pointer]
                - generic [ref=e759]:
                  - term [ref=e760]: accept
                  - definition [ref=e761]:
                    - generic [ref=e762]:
                      - code [ref=e765]: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
                      - button "Copy to clipboard" [ref=e767] [cursor=pointer]
                - generic [ref=e770]:
                  - term [ref=e771]: sec-fetch-site
                  - definition [ref=e772]:
                    - generic [ref=e773]:
                      - code [ref=e776]: none
                      - button "Copy to clipboard" [ref=e778] [cursor=pointer]
                - generic [ref=e781]:
                  - term [ref=e782]: sec-fetch-mode
                  - definition [ref=e783]:
                    - generic [ref=e784]:
                      - code [ref=e787]: navigate
                      - button "Copy to clipboard" [ref=e789] [cursor=pointer]
                - generic [ref=e792]:
                  - term [ref=e793]: sec-fetch-user
                  - definition [ref=e794]:
                    - generic [ref=e795]:
                      - code [ref=e798]: "?1"
                      - button "Copy to clipboard" [ref=e800] [cursor=pointer]
                - generic [ref=e803]:
                  - term [ref=e804]: sec-fetch-dest
                  - definition [ref=e805]:
                    - generic [ref=e806]:
                      - code [ref=e809]: document
                      - button "Copy to clipboard" [ref=e811] [cursor=pointer]
                - generic [ref=e814]:
                  - term [ref=e815]: accept-encoding
                  - definition [ref=e816]:
                    - generic [ref=e817]:
                      - code [ref=e820]: gzip, deflate, br, zstd
                      - button "Copy to clipboard" [ref=e822] [cursor=pointer]
            - generic [ref=e825]:
              - heading "Body" [level=1] [ref=e826]
              - generic [ref=e830]:
                - code [ref=e833]: "[]"
                - button "Copy to clipboard" [ref=e835] [cursor=pointer]
        - generic [ref=e838]:
          - heading "Context" [level=2] [ref=e839]
          - generic [ref=e841]:
            - heading "Versions" [level=1] [ref=e842]
            - generic [ref=e846]:
              - generic [ref=e847]:
                - term [ref=e848]: Php Version
                - definition [ref=e849]:
                  - generic [ref=e850]:
                    - code [ref=e853]: 8.5.10
                    - button "Copy to clipboard" [ref=e855] [cursor=pointer]
              - generic [ref=e858]:
                - term [ref=e859]: Laravel Version
                - definition [ref=e860]:
                  - generic [ref=e861]:
                    - code [ref=e864]: 12.71.0
                    - button "Copy to clipboard" [ref=e866] [cursor=pointer]
              - generic [ref=e869]:
                - term [ref=e870]: Laravel Locale
                - definition [ref=e871]:
                  - generic [ref=e872]:
                    - code [ref=e875]: en
                    - button "Copy to clipboard" [ref=e877] [cursor=pointer]
              - generic [ref=e880]:
                - term [ref=e881]: Laravel Config Cached
                - definition [ref=e882]:
                  - generic [ref=e883]: "false"
              - generic [ref=e887]:
                - term [ref=e888]: App Debug
                - definition [ref=e889]:
                  - generic [ref=e890]: "true"
              - generic [ref=e894]:
                - term [ref=e895]: App Env
                - definition [ref=e896]:
                  - generic [ref=e897]:
                    - code [ref=e900]: local
                    - button "Copy to clipboard" [ref=e902] [cursor=pointer]
    - generic [ref=e906]:
      - navigation:
        - list [ref=e907]:
          - listitem [ref=e908]:
            - button "11 Queries" [ref=e909] [cursor=pointer]:
              - generic [ref=e910]: "11"
              - generic [ref=e911]: Queries
      - generic [ref=e912]:
        - generic [ref=e913]:
          - generic [ref=e914]:
            - generic [ref=e915]: 10:28:20 AM
            - generic [ref=e916]:
              - img "Runtime" [ref=e917]
              - text: 4.94ms
            - generic [ref=e919]:
              - img "Connection" [ref=e920]
              - text: mysql
          - generic [ref=e923] [cursor=pointer]:
            - code [ref=e926]:
              - code [ref=e928]: "select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'settings' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`"
            - button "Copy to clipboard" [ref=e930]
            - button [ref=e933]
        - generic [ref=e936]:
          - generic [ref=e937]:
            - generic [ref=e938]: 10:28:20 AM
            - generic [ref=e939]:
              - img "Runtime" [ref=e940]
              - text: 0.68ms
            - generic [ref=e942]:
              - img "Connection" [ref=e943]
              - text: mysql
          - generic [ref=e946] [cursor=pointer]:
            - code [ref=e949]:
              - code [ref=e951]: "select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'cache' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`"
            - button "Copy to clipboard" [ref=e953]
            - button [ref=e956]
        - generic [ref=e959]:
          - generic [ref=e960]:
            - generic [ref=e961]: 10:28:20 AM
            - generic [ref=e962]:
              - img "Runtime" [ref=e963]
              - text: 0.63ms
            - generic [ref=e965]:
              - img "Connection" [ref=e966]
              - text: mysql
          - generic [ref=e969]:
            - generic [ref=e970] [cursor=pointer]:
              - code [ref=e973]:
                - code [ref=e975]: "select * from `cache` where `key` in (smart_african_market_cache_mail.configured_from_db)"
              - button "Copy to clipboard" [ref=e977]
              - button [ref=e980]
            - button "1 query parameter" [ref=e984] [cursor=pointer]
        - generic [ref=e987]:
          - generic [ref=e988]:
            - generic [ref=e989]: 10:28:20 AM
            - generic [ref=e990]:
              - img "Runtime" [ref=e991]
              - text: 2.21ms
            - generic [ref=e993]:
              - img "Connection" [ref=e994]
              - text: mysql
          - generic [ref=e997]:
            - generic [ref=e998] [cursor=pointer]:
              - code [ref=e1001]:
                - code [ref=e1003]: "select * from `settings` where `key` = mail_host limit 1"
              - button "Copy to clipboard" [ref=e1005]
              - button [ref=e1008]
            - button "1 query parameter" [ref=e1012] [cursor=pointer]
        - generic [ref=e1015]:
          - generic [ref=e1016]:
            - generic [ref=e1017]: 10:28:20 AM
            - generic [ref=e1018]:
              - img "Runtime" [ref=e1019]
              - text: 3.58ms
            - generic [ref=e1021]:
              - img "Connection" [ref=e1022]
              - text: mysql
          - generic [ref=e1025]:
            - generic [ref=e1026] [cursor=pointer]:
              - code [ref=e1029]:
                - code [ref=e1031]: "select * from `sessions` where `id` = wfUdOSYbQubwa315dPghGFWetz84n9wcagdZ2DRg limit 1"
              - button "Copy to clipboard" [ref=e1033]
              - button [ref=e1036]
            - button "1 query parameter" [ref=e1040] [cursor=pointer]
        - generic [ref=e1043]:
          - generic [ref=e1044]:
            - generic [ref=e1045]: 10:28:20 AM
            - generic [ref=e1046]:
              - img "Runtime" [ref=e1047]
              - text: 0.83ms
            - generic [ref=e1049]:
              - img "Connection" [ref=e1050]
              - text: mysql
          - generic [ref=e1053]:
            - generic [ref=e1054] [cursor=pointer]:
              - code [ref=e1057]:
                - code [ref=e1059]: "select * from `cache` where `key` in (smart_african_market_cache_maintenance_mode)"
              - button "Copy to clipboard" [ref=e1061]
              - button [ref=e1064]
            - button "1 query parameter" [ref=e1068] [cursor=pointer]
        - generic [ref=e1071]:
          - generic [ref=e1072]:
            - generic [ref=e1073]: 10:28:20 AM
            - generic [ref=e1074]:
              - img "Runtime" [ref=e1075]
              - text: 0.97ms
            - generic [ref=e1077]:
              - img "Connection" [ref=e1078]
              - text: mysql
          - generic [ref=e1081]:
            - generic [ref=e1082] [cursor=pointer]:
              - code [ref=e1085]:
                - code [ref=e1087]: "select * from `vendor_service_categories` where `slug` = home-services and `is_active` = true limit 1"
              - button "Copy to clipboard" [ref=e1089]
              - button [ref=e1092]
            - button "2 query parameters" [ref=e1096] [cursor=pointer]
        - generic [ref=e1099]:
          - generic [ref=e1100]:
            - generic [ref=e1101]: 10:28:20 AM
            - generic [ref=e1102]:
              - img "Runtime" [ref=e1103]
              - text: 2.96ms
            - generic [ref=e1105]:
              - img "Connection" [ref=e1106]
              - text: mysql
          - generic [ref=e1109]:
            - generic [ref=e1110] [cursor=pointer]:
              - code [ref=e1113]:
                - code [ref=e1115]: "select * from `vendor_service_categories` where `vendor_service_categories`.`parent_id` in (32) and `is_active` = true order by `sort_order` asc, `name` asc, `sort_order` asc, `name` asc"
              - button "Copy to clipboard" [ref=e1117]
              - button [ref=e1120]
            - button "1 query parameter" [ref=e1124] [cursor=pointer]
        - generic [ref=e1127]:
          - generic [ref=e1128]:
            - generic [ref=e1129]: 10:28:20 AM
            - generic [ref=e1130]:
              - img "Runtime" [ref=e1131]
              - text: 0.98ms
            - generic [ref=e1133]:
              - img "Connection" [ref=e1134]
              - text: mysql
          - generic [ref=e1137]:
            - generic [ref=e1138] [cursor=pointer]:
              - code [ref=e1141]:
                - code [ref=e1143]: "select count(*) as aggregate from `vendor_services` where `status` = available and `vendor_service_category_id` in (39, 40, 41)"
              - button "Copy to clipboard" [ref=e1145]
              - button [ref=e1148]
            - button "4 query parameters" [ref=e1152] [cursor=pointer]
        - generic [ref=e1155]:
          - generic [ref=e1156]:
            - generic [ref=e1157]: 10:28:20 AM
            - generic [ref=e1158]:
              - img "Runtime" [ref=e1159]
              - text: 0.96ms
            - generic [ref=e1161]:
              - img "Connection" [ref=e1162]
              - text: mysql
          - generic [ref=e1165]:
            - generic [ref=e1166] [cursor=pointer]:
              - code [ref=e1169]:
                - code [ref=e1171]: "select * from `vendor_service_categories` where `is_active` = true and `parent_id` is null order by `sort_order` asc, `name` asc"
              - button "Copy to clipboard" [ref=e1173]
              - button [ref=e1176]
            - button "1 query parameter" [ref=e1180] [cursor=pointer]
        - generic [ref=e1183]:
          - generic [ref=e1184]:
            - generic [ref=e1185]: 10:28:20 AM
            - generic [ref=e1186]:
              - img "Runtime" [ref=e1187]
              - text: 0.91ms
            - generic [ref=e1189]:
              - img "Connection" [ref=e1190]
              - text: mysql
          - generic [ref=e1193]:
            - generic [ref=e1194] [cursor=pointer]:
              - code [ref=e1197]:
                - code [ref=e1199]: "select * from `vendor_service_categories` where `vendor_service_categories`.`parent_id` in (30, 31, 32, 33) and `is_active` = true order by `sort_order` asc, `name` asc, `sort_order` asc, `name` asc"
              - button "Copy to clipboard" [ref=e1201]
              - button [ref=e1204]
            - button "1 query parameter" [ref=e1208] [cursor=pointer]
    - generic [ref=e1212]:
      - list [ref=e1213]:
        - listitem [ref=e1214]
        - listitem [ref=e1223]: ·
        - listitem [ref=e1224]:
          - link "Source" [ref=e1225] [cursor=pointer]:
            - /url: https://github.com/spatie/laravel-ignition
        - listitem [ref=e1226]: ·
        - listitem [ref=e1227]:
          - link "Docs" [ref=e1228] [cursor=pointer]:
            - /url: https://flareapp.io/docs/ignition/introducing-ignition/overview
        - listitem [ref=e1229]: ·
        - listitem [ref=e1230]:
          - link "Laravel" [ref=e1231] [cursor=pointer]:
            - /url: https://laravel.com
      - paragraph [ref=e1233]:
        - text: Ignition is built by
        - link "Flare" [ref=e1234] [cursor=pointer]:
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
> 69  |         expect(response.status()).toBe(200);
      |                                   ^ Error: expect(received).toBe(expected) // Object.is equality
  70  |         await expect(page).toHaveTitle(/Services/);
  71  |       });
  72  |     }
  73  |   });
  74  | 
  75  |   test.describe('Sub-category pages', () => {
  76  |     for (const slug of subCategories) {
  77  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  78  |         const response = await page.goto(`/services/${slug}`);
  79  |         expect(response.status()).toBe(200);
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