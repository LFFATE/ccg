<?php return 'Usage: php ccg.php [generator] [command] [options]



[32maddon-xml create[0m
             creates addonXml structure and write it to file
             @throws Exception if file already exists



[32maddon-xml remove[0m
             removes file addon.xml
             @throws Exception if file doesn\'t exists



[32maddon-xml/update --addon.id <addon_id> --set <item> [...args][0m
             Sets additional field to addon xml file
             addon.id - id of the addon
             ---
                  --set
                      settings-item - <item id="date">...</item>
                          args: --section <section_id> --type <type> --id <id> [--default_value <default_value>] [--variants "<variant1,variant2>"]
                              section         - id for the settings section
                              type            - type of the item id: input, textarea, password, checkbox, selectbox, multiple select, multiple checkboxes, countries list, states list, file, info, header, template
                              id              - id of the setting item
                              default_value   - default value for setting item
                              variants        - list of item value variants comma separated and quote wrapped
             ---
             
             see more @link [https://www.cs-cart.ru/docs/4.9.x/developer_guide/addons/scheme/scheme3.0_structure.html]
             @throws Exception if file doesn\'t exists
';
