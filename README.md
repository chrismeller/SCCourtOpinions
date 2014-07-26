About
=====
The South Carolina Judicial Department publishes all opinions issued by the Appeals and Supreme Courts of South Carolina on their website as PDFs, but there is no convenient way to follow those updates.

In the spirit of the growing trend, I decided to fix that by page-scraping the data and pushing it to a Twitter account. [@SCCourtOpinions](http://twitter.com/SCCourtOpinions) was born!

How
---
Grab the contents, parse it with PHP's [DOM](http://php.net/dom), and spit it out as a rough [Atom](http://en.wikipedia.org/wiki/Atom_(standard)) feed. You can, of course, just get the opinions and spit them out any way you like.

In addition to the Atom feeds, there are also two `export_*` scripts that show a basic usage and simply spit out the result. I recommend running them at the command line (`php export_appeals.php`).
