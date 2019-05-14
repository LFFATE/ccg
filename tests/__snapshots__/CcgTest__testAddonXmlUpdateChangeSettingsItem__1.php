<?php return '<?xml version="1.0"?>
<addon scheme="3.0" edition_type="ROOT,ULT:VENDOR">
  <id>sd_new_addon</id>
  <version>4.8.1</version>
  <priority>1000</priority>
  <status>active</status>
  <auto_install>MULTIVENDOR,ULTIMATE</auto_install>
  <settings>
    <sections>
      <section id="general">
        <items>
          <item id="default_name">
            <type>selectbox</type>
            <variants>
              <item id="Daniels"/>
              <item id="Jack"/>
              <item id="Margarita"/>
              <item id="Martini"/>
            </variants>
            <default_value>Jack</default_value>
          </item>
        </items>
      </section>
    </sections>
  </settings>
</addon>
';
