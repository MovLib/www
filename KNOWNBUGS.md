# Known Bugs

## 0.0.1-dev
* The `/public/asset/img/star-awesome.svg` doesn’t work as expected in WebKit because it’s not passing the fragment to
  the SVG and therefor breaks the `:target` selector in the SVG styles. No known workaround.
* Gmail refuses to accept IPv6 emails due to wrong reverse DNS look-up. Have a look at [this Google help
  article](https://support.google.com/mail/answer/81126?p=ipv6_authentication_error&rd=1#authentication).

### Solved
* Internet Explorer isn't displaying SVG images embedded via img tags
  * The solution is to set the `viewBox` attribute on the root element, rather than `width` and `height`.
    <sup>[[ref](http://stackoverflow.com/questions/9777143)]</sup>
* Safari displays SVGs totally distorted.
  * The solution is to set the `preserveAspectRatio` attribute to `xMinYMin none`.
    <sup>[[ref](http://stackoverflow.com/questions/11768364)]</sup>
