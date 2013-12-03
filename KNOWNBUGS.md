# Known Bugs

## 0.0.1-dev
* Gmail refuses to accept IPv6 emails due to wrong reverse DNS look-up. Have a look at [this Google help article](https://support.google.com/mail/answer/81126?p=ipv6_authentication_error&rd=1#authentication).

### Solved
* Internet Explorer isn't displaying SVG images embedded via img tags
  * The solution is to set the viewBox attribute on the root element, rather than the width and height. <sup>[[ref](http://stackoverflow.com/questions/9777143)]</sup>
* Safari displays SVGs totally distorted.
  * The solution is to set the preserveAspectRatio attribute to "xMinYMin none". <sup>[[ref](http://stackoverflow.com/questions/11768364)]</sup>
