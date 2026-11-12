# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: service-category-pages.spec.ts >> Service Category Pages >> Sub-category pages >> loads /services/car-garage without errors
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
                            - text: array:2 [
                            - generic "[Ctrl+click] Expand all children" [ref=e598] [cursor=pointer]: ▼
                            - generic [ref=e600]:
                              - text: 0 =>
                              - generic "App\\Models\\VendorServiceCategory" [ref=e601]:
                                - generic [ref=e602]: App\Models
                                - generic [ref=e603]: \
                                - text: VendorServiceCategory
                              - text: "{"
                              - link "#2831 ▶" [ref=e604] [cursor=pointer]:
                                - /url: "#sf-dump-225546392-ref22831"
                                - text: "#2831"
                                - generic [ref=e605]: ▶
                              - text: "} 1 =>"
                              - generic "App\\Models\\VendorServiceCategory" [ref=e606]:
                                - generic [ref=e607]: App\Models
                                - generic [ref=e608]: \
                                - text: VendorServiceCategory
                              - text: "{"
                              - generic "[Ctrl+click] Expand all children" [ref=e609] [cursor=pointer]:
                                - text: "#2839"
                                - generic [ref=e610]: ▶
                              - text: "}"
                            - text: "]"
                      - generic [ref=e611]:
                        - term [ref=e612]: services
                        - definition [ref=e613]:
                          - generic [ref=e615]:
                            - text: "Illuminate\\Pagination\\LengthAwarePaginator {"
                            - generic "[Ctrl+click] Expand all children" [ref=e616] [cursor=pointer]:
                              - text: "#2410"
                              - generic [ref=e617]: ▼
                            - generic [ref=e618]:
                              - text: "#items:"
                              - generic "Illuminate\\Database\\Eloquent\\Collection" [ref=e619]:
                                - generic [ref=e620]: Illuminate\Database\Eloquent
                                - generic [ref=e621]: \
                                - text: Collection
                              - text: "{"
                              - generic "[Ctrl+click] Expand all children" [ref=e622] [cursor=pointer]:
                                - text: "#2854"
                                - generic [ref=e623]: ▶
                              - text: "} #perPage: 12 #currentPage: 1 #path: \"http://localhost:8000/services/car-garage\" #query: [] #fragment: null #pageName: \"page\" #escapeWhenCastingToString: false +onEachSide: 3 #options: array:2 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e624] [cursor=pointer]: ▶
                              - text: "] #total: 0 #lastPage: 1"
                            - text: "}"
                      - generic [ref=e626]:
                        - term [ref=e627]: parentCategories
                        - definition [ref=e628]:
                          - generic [ref=e630]:
                            - text: "Illuminate\\Database\\Eloquent\\Collection {"
                            - generic "[Ctrl+click] Expand all children" [ref=e631] [cursor=pointer]:
                              - text: "#2414"
                              - generic [ref=e632]: ▼
                            - generic [ref=e633]:
                              - text: "#items: array:4 ["
                              - generic "[Ctrl+click] Expand all children" [ref=e634] [cursor=pointer]: ▶
                              - text: "] #escapeWhenCastingToString: false"
                            - text: "}"
        - generic [ref=e636]:
          - heading "Request" [level=2] [ref=e637]
          - generic [ref=e638]:
            - generic [ref=e639]:
              - generic [ref=e640]:
                - generic [ref=e641]: http://localhost:8000/services/car-garage
                - generic [ref=e642]: GET
              - generic [ref=e644] [cursor=pointer]:
                - code [ref=e647]:
                  - code [ref=e649]: "curl \"http://localhost:8000/services/car-garage\" \\ -X GET \\ -H 'host: localhost:8000' \\ -H 'connection: keep-alive' \\ -H 'sec-ch-ua: \"Chromium\";v=\"158\", \"Not.A/Brand\";v=\"24\", \"HeadlessChrome\";v=\"158\"' \\ -H 'sec-ch-ua-mobile: ?0' \\ -H 'sec-ch-ua-platform: \"Windows\"' \\ -H 'upgrade-insecure-requests: 1' \\ -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36' \\ -H 'accept-language: en-US' \\ -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' \\ -H 'sec-fetch-site: none' \\ -H 'sec-fetch-mode: navigate' \\ -H 'sec-fetch-user: ?1' \\ -H 'sec-fetch-dest: document' \\ -H 'accept-encoding: gzip, deflate, br, zstd';"
                - button "Copy to clipboard" [ref=e651]
                - button [ref=e654]
            - generic [ref=e657]:
              - heading "Browser" [level=1] [ref=e658]
              - generic [ref=e662]:
                - code [ref=e665]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36
                - button "Copy to clipboard" [ref=e667] [cursor=pointer]
            - generic [ref=e670]:
              - heading "Headers" [level=1] [ref=e671]
              - generic [ref=e675]:
                - generic [ref=e676]:
                  - term [ref=e677]: host
                  - definition [ref=e678]:
                    - generic [ref=e679]:
                      - code [ref=e682]: localhost:8000
                      - button "Copy to clipboard" [ref=e684] [cursor=pointer]
                - generic [ref=e687]:
                  - term [ref=e688]: connection
                  - definition [ref=e689]:
                    - generic [ref=e690]:
                      - code [ref=e693]: keep-alive
                      - button "Copy to clipboard" [ref=e695] [cursor=pointer]
                - generic [ref=e698]:
                  - term [ref=e699]: sec-ch-ua
                  - definition [ref=e700]:
                    - generic [ref=e701]:
                      - code [ref=e704]: "\"Chromium\";v=\"158\", \"Not.A/Brand\";v=\"24\", \"HeadlessChrome\";v=\"158\""
                      - button "Copy to clipboard" [ref=e706] [cursor=pointer]
                - generic [ref=e709]:
                  - term [ref=e710]: sec-ch-ua-mobile
                  - definition [ref=e711]:
                    - generic [ref=e712]:
                      - code [ref=e715]: "?0"
                      - button "Copy to clipboard" [ref=e717] [cursor=pointer]
                - generic [ref=e720]:
                  - term [ref=e721]: sec-ch-ua-platform
                  - definition [ref=e722]:
                    - generic [ref=e723]:
                      - code [ref=e726]: "\"Windows\""
                      - button "Copy to clipboard" [ref=e728] [cursor=pointer]
                - generic [ref=e731]:
                  - term [ref=e732]: upgrade-insecure-requests
                  - definition [ref=e733]:
                    - generic [ref=e734]:
                      - code [ref=e737]: "1"
                      - button "Copy to clipboard" [ref=e739] [cursor=pointer]
                - generic [ref=e742]:
                  - term [ref=e743]: user-agent
                  - definition [ref=e744]:
                    - generic [ref=e745]:
                      - code [ref=e748]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.8127.18 Safari/537.36
                      - button "Copy to clipboard" [ref=e750] [cursor=pointer]
                - generic [ref=e753]:
                  - term [ref=e754]: accept-language
                  - definition [ref=e755]:
                    - generic [ref=e756]:
                      - code [ref=e759]: en-US
                      - button "Copy to clipboard" [ref=e761] [cursor=pointer]
                - generic [ref=e764]:
                  - term [ref=e765]: accept
                  - definition [ref=e766]:
                    - generic [ref=e767]:
                      - code [ref=e770]: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
                      - button "Copy to clipboard" [ref=e772] [cursor=pointer]
                - generic [ref=e775]:
                  - term [ref=e776]: sec-fetch-site
                  - definition [ref=e777]:
                    - generic [ref=e778]:
                      - code [ref=e781]: none
                      - button "Copy to clipboard" [ref=e783] [cursor=pointer]
                - generic [ref=e786]:
                  - term [ref=e787]: sec-fetch-mode
                  - definition [ref=e788]:
                    - generic [ref=e789]:
                      - code [ref=e792]: navigate
                      - button "Copy to clipboard" [ref=e794] [cursor=pointer]
                - generic [ref=e797]:
                  - term [ref=e798]: sec-fetch-user
                  - definition [ref=e799]:
                    - generic [ref=e800]:
                      - code [ref=e803]: "?1"
                      - button "Copy to clipboard" [ref=e805] [cursor=pointer]
                - generic [ref=e808]:
                  - term [ref=e809]: sec-fetch-dest
                  - definition [ref=e810]:
                    - generic [ref=e811]:
                      - code [ref=e814]: document
                      - button "Copy to clipboard" [ref=e816] [cursor=pointer]
                - generic [ref=e819]:
                  - term [ref=e820]: accept-encoding
                  - definition [ref=e821]:
                    - generic [ref=e822]:
                      - code [ref=e825]: gzip, deflate, br, zstd
                      - button "Copy to clipboard" [ref=e827] [cursor=pointer]
            - generic [ref=e830]:
              - heading "Body" [level=1] [ref=e831]
              - generic [ref=e835]:
                - code [ref=e838]: "[]"
                - button "Copy to clipboard" [ref=e840] [cursor=pointer]
        - generic [ref=e843]:
          - heading "Context" [level=2] [ref=e844]
          - generic [ref=e846]:
            - heading "Versions" [level=1] [ref=e847]
            - generic [ref=e851]:
              - generic [ref=e852]:
                - term [ref=e853]: Php Version
                - definition [ref=e854]:
                  - generic [ref=e855]:
                    - code [ref=e858]: 8.5.10
                    - button "Copy to clipboard" [ref=e860] [cursor=pointer]
              - generic [ref=e863]:
                - term [ref=e864]: Laravel Version
                - definition [ref=e865]:
                  - generic [ref=e866]:
                    - code [ref=e869]: 12.71.0
                    - button "Copy to clipboard" [ref=e871] [cursor=pointer]
              - generic [ref=e874]:
                - term [ref=e875]: Laravel Locale
                - definition [ref=e876]:
                  - generic [ref=e877]:
                    - code [ref=e880]: en
                    - button "Copy to clipboard" [ref=e882] [cursor=pointer]
              - generic [ref=e885]:
                - term [ref=e886]: Laravel Config Cached
                - definition [ref=e887]:
                  - generic [ref=e888]: "false"
              - generic [ref=e892]:
                - term [ref=e893]: App Debug
                - definition [ref=e894]:
                  - generic [ref=e895]: "true"
              - generic [ref=e899]:
                - term [ref=e900]: App Env
                - definition [ref=e901]:
                  - generic [ref=e902]:
                    - code [ref=e905]: local
                    - button "Copy to clipboard" [ref=e907] [cursor=pointer]
    - generic [ref=e911]:
      - navigation:
        - list [ref=e912]:
          - listitem [ref=e913]:
            - button "12 Queries" [ref=e914] [cursor=pointer]:
              - generic [ref=e915]: "12"
              - generic [ref=e916]: Queries
      - generic [ref=e917]:
        - generic [ref=e918]:
          - generic [ref=e919]:
            - generic [ref=e920]: 10:28:37 AM
            - generic [ref=e921]:
              - img "Runtime" [ref=e922]
              - text: 15.33ms
            - generic [ref=e924]:
              - img "Connection" [ref=e925]
              - text: mysql
          - generic [ref=e928] [cursor=pointer]:
            - code [ref=e931]:
              - code [ref=e933]: "select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'settings' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`"
            - button "Copy to clipboard" [ref=e935]
            - button [ref=e938]
        - generic [ref=e941]:
          - generic [ref=e942]:
            - generic [ref=e943]: 10:28:37 AM
            - generic [ref=e944]:
              - img "Runtime" [ref=e945]
              - text: 0.96ms
            - generic [ref=e947]:
              - img "Connection" [ref=e948]
              - text: mysql
          - generic [ref=e951] [cursor=pointer]:
            - code [ref=e954]:
              - code [ref=e956]: "select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'cache' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`"
            - button "Copy to clipboard" [ref=e958]
            - button [ref=e961]
        - generic [ref=e964]:
          - generic [ref=e965]:
            - generic [ref=e966]: 10:28:37 AM
            - generic [ref=e967]:
              - img "Runtime" [ref=e968]
              - text: 0.86ms
            - generic [ref=e970]:
              - img "Connection" [ref=e971]
              - text: mysql
          - generic [ref=e974]:
            - generic [ref=e975] [cursor=pointer]:
              - code [ref=e978]:
                - code [ref=e980]: "select * from `cache` where `key` in (smart_african_market_cache_mail.configured_from_db)"
              - button "Copy to clipboard" [ref=e982]
              - button [ref=e985]
            - button "1 query parameter" [ref=e989] [cursor=pointer]
        - generic [ref=e992]:
          - generic [ref=e993]:
            - generic [ref=e994]: 10:28:37 AM
            - generic [ref=e995]:
              - img "Runtime" [ref=e996]
              - text: 0.85ms
            - generic [ref=e998]:
              - img "Connection" [ref=e999]
              - text: mysql
          - generic [ref=e1002]:
            - generic [ref=e1003] [cursor=pointer]:
              - code [ref=e1006]:
                - code [ref=e1008]: "select * from `settings` where `key` = mail_host limit 1"
              - button "Copy to clipboard" [ref=e1010]
              - button [ref=e1013]
            - button "1 query parameter" [ref=e1017] [cursor=pointer]
        - generic [ref=e1020]:
          - generic [ref=e1021]:
            - generic [ref=e1022]: 10:28:37 AM
            - generic [ref=e1023]:
              - img "Runtime" [ref=e1024]
              - text: 14.99ms
            - generic [ref=e1026]:
              - img "Connection" [ref=e1027]
              - text: mysql
          - generic [ref=e1030]:
            - generic [ref=e1031] [cursor=pointer]:
              - code [ref=e1034]:
                - code [ref=e1036]: "select * from `sessions` where `id` = ALULEpFEIrmy41e8R30NkwaSgPHvtqEIWfgiw2ik limit 1"
              - button "Copy to clipboard" [ref=e1038]
              - button [ref=e1041]
            - button "1 query parameter" [ref=e1045] [cursor=pointer]
        - generic [ref=e1048]:
          - generic [ref=e1049]:
            - generic [ref=e1050]: 10:28:37 AM
            - generic [ref=e1051]:
              - img "Runtime" [ref=e1052]
              - text: 16.86ms
            - generic [ref=e1054]:
              - img "Connection" [ref=e1055]
              - text: mysql
          - generic [ref=e1058]:
            - generic [ref=e1059] [cursor=pointer]:
              - code [ref=e1062]:
                - code [ref=e1064]: "select * from `cache` where `key` in (smart_african_market_cache_maintenance_mode)"
              - button "Copy to clipboard" [ref=e1066]
              - button [ref=e1069]
            - button "1 query parameter" [ref=e1073] [cursor=pointer]
        - generic [ref=e1076]:
          - generic [ref=e1077]:
            - generic [ref=e1078]: 10:28:37 AM
            - generic [ref=e1079]:
              - img "Runtime" [ref=e1080]
              - text: 23.98ms
            - generic [ref=e1082]:
              - img "Connection" [ref=e1083]
              - text: mysql
          - generic [ref=e1086]:
            - generic [ref=e1087] [cursor=pointer]:
              - code [ref=e1090]:
                - code [ref=e1092]: "select * from `vendor_service_categories` where `slug` = car-garage and `is_active` = true limit 1"
              - button "Copy to clipboard" [ref=e1094]
              - button [ref=e1097]
            - button "2 query parameters" [ref=e1101] [cursor=pointer]
        - generic [ref=e1104]:
          - generic [ref=e1105]:
            - generic [ref=e1106]: 10:28:37 AM
            - generic [ref=e1107]:
              - img "Runtime" [ref=e1108]
              - text: 15.34ms
            - generic [ref=e1110]:
              - img "Connection" [ref=e1111]
              - text: mysql
          - generic [ref=e1114] [cursor=pointer]:
            - code [ref=e1117]:
              - code [ref=e1119]: "select * from `vendor_service_categories` where `vendor_service_categories`.`id` in (31)"
            - button "Copy to clipboard" [ref=e1121]
            - button [ref=e1124]
        - generic [ref=e1127]:
          - generic [ref=e1128]:
            - generic [ref=e1129]: 10:28:37 AM
            - generic [ref=e1130]:
              - img "Runtime" [ref=e1131]
              - text: 16.39ms
            - generic [ref=e1133]:
              - img "Connection" [ref=e1134]
              - text: mysql
          - generic [ref=e1137]:
            - generic [ref=e1138] [cursor=pointer]:
              - code [ref=e1141]:
                - code [ref=e1143]: "select * from `vendor_service_categories` where `vendor_service_categories`.`parent_id` in (37) and `is_active` = true order by `sort_order` asc, `name` asc, `sort_order` asc, `name` asc"
              - button "Copy to clipboard" [ref=e1145]
              - button [ref=e1148]
            - button "1 query parameter" [ref=e1152] [cursor=pointer]
        - generic [ref=e1155]:
          - generic [ref=e1156]:
            - generic [ref=e1157]: 10:28:37 AM
            - generic [ref=e1158]:
              - img "Runtime" [ref=e1159]
              - text: 13.48ms
            - generic [ref=e1161]:
              - img "Connection" [ref=e1162]
              - text: mysql
          - generic [ref=e1165]:
            - generic [ref=e1166] [cursor=pointer]:
              - code [ref=e1169]:
                - code [ref=e1171]: "select count(*) as aggregate from `vendor_services` where `status` = available and `vendor_service_category_id` in (13, 14, 15, 16)"
              - button "Copy to clipboard" [ref=e1173]
              - button [ref=e1176]
            - button "5 query parameters" [ref=e1180] [cursor=pointer]
        - generic [ref=e1183]:
          - generic [ref=e1184]:
            - generic [ref=e1185]: 10:28:37 AM
            - generic [ref=e1186]:
              - img "Runtime" [ref=e1187]
              - text: 2.91ms
            - generic [ref=e1189]:
              - img "Connection" [ref=e1190]
              - text: mysql
          - generic [ref=e1193]:
            - generic [ref=e1194] [cursor=pointer]:
              - code [ref=e1197]:
                - code [ref=e1199]: "select * from `vendor_service_categories` where `is_active` = true and `parent_id` is null order by `sort_order` asc, `name` asc"
              - button "Copy to clipboard" [ref=e1201]
              - button [ref=e1204]
            - button "1 query parameter" [ref=e1208] [cursor=pointer]
        - generic [ref=e1211]:
          - generic [ref=e1212]:
            - generic [ref=e1213]: 10:28:37 AM
            - generic [ref=e1214]:
              - img "Runtime" [ref=e1215]
              - text: 20.13ms
            - generic [ref=e1217]:
              - img "Connection" [ref=e1218]
              - text: mysql
          - generic [ref=e1221]:
            - generic [ref=e1222] [cursor=pointer]:
              - code [ref=e1225]:
                - code [ref=e1227]: "select * from `vendor_service_categories` where `vendor_service_categories`.`parent_id` in (30, 31, 32, 33) and `is_active` = true order by `sort_order` asc, `name` asc, `sort_order` asc, `name` asc"
              - button "Copy to clipboard" [ref=e1229]
              - button [ref=e1232]
            - button "1 query parameter" [ref=e1236] [cursor=pointer]
    - generic [ref=e1240]:
      - list [ref=e1241]:
        - listitem [ref=e1242]
        - listitem [ref=e1251]: ·
        - listitem [ref=e1252]:
          - link "Source" [ref=e1253] [cursor=pointer]:
            - /url: https://github.com/spatie/laravel-ignition
        - listitem [ref=e1254]: ·
        - listitem [ref=e1255]:
          - link "Docs" [ref=e1256] [cursor=pointer]:
            - /url: https://flareapp.io/docs/ignition/introducing-ignition/overview
        - listitem [ref=e1257]: ·
        - listitem [ref=e1258]:
          - link "Laravel" [ref=e1259] [cursor=pointer]:
            - /url: https://laravel.com
      - paragraph [ref=e1261]:
        - text: Ignition is built by
        - link "Flare" [ref=e1262] [cursor=pointer]:
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