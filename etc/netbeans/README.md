The NetBeans configuration files are usually stored in the following directory:

* Windows: `C:\Users\<username>\AppData\Roaming\NetBeans\<version>\config`
* Linux: `/unknown`
* Mac: `/unknown`

You can further include the following for the `.nbattrs` file in the `Templates/Licenses` directory:

```XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE attributes PUBLIC "-//NetBeans//DTD DefaultAttributes 1.0//EN" "http://www.netbeans.org/dtds/attributes-1_0.dtd">
<attributes version="1.0">
    <fileobject name="license-movlib.txt">
        <attr name="displayName" stringvalue="MovLib"/>
        <attr name="mavenLicenseURL" stringvalue="https://www.gnu.org/licenses/agpl.html"/>
        <attr name="template" boolvalue="true"/>
    </fileobject>
</attributes>
```
