# nginx configuration
Nginx configuration used at [MovLib](http://movlib.org/). MovLib is meant to be running in a root or VPS environment.
Therefor we keep the configuration files within our application for easy deployment (even after software upgrades).

## `deploy.json`
Defines in which order files are concatenated by our deploy script. It’s a simple array and files will be concatenated
in the order they appear within the array. Comments will be removed before deploying.

## `routes.json`
Defines the URL mapping of our application. To lower the repetition of writing location block for location block we use
the `routes.json` file to automatically convert the rules for us. By leaving out options that are bad for the performance
of our web server this also helps us in not making too many mistakes. The syntax isn’t really simple (yet), but we hope to
find a way to improve this soon.

```json
{
  "location": {
    "instruction": "instruction parameter",
    ...
    "vars": {
      "var key": "var value"
      ...
    }
  },
  ...
}
```

* __location:__ This contains exactly the same as you are used to write directly after the
[`location`](http://nginx.org/en/docs/http/ngx_http_core_module.html#location) instruction in your nginx configurations.
Please refer to the official documentation for an in-depth explanation.
* __instruction (and parameter):__ Special instructions which tells our deploy script what it has to do. The parameter
usually contains the variable part of any nginx instruction. Possible instructions are:
  * __presenter:__ Set the class name of the PHP presenter. Possible values are all class names found in the presenter
    namespace of our PHP application (beware of abstract classes).
  * __return:__ Directly return with the following parameter. Possible values contain everything you are used to use
    in conjunction with the nginx [`return`](http://nginx.org/en/docs/http/ngx_http_rewrite_module.html#return)
    instruction (e.g. `"301 /"`).
* __vars:__ Used to export RegEx groups to FastCGI parameters, example:

```json
{
  "~ /movie/([0-9]+)$": {
    "vars": {
      "movie_id": "$1"
    }
  }
}
```