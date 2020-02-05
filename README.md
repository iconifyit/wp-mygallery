# WP MyGallery

A simple alternative to WordPress's built-in Gallery builder. The main difference here is that the gallery adds image metadata automatically based on the image file name. So if you name your image file 'awesome-dog-running-photo.png', the plugin will convert the dash-delimited words into title and alt text in senence for : 'Photo of awesome dog running'. This is intended for image creators such as icon designers to be able to quickly add previews of their icons to their site and meet SEO requirements (alt text) and meaningful image URLs. Additionally, the plugin generates an Image SiteMap file for submission to search engines.

## Usage

Create a gallery the same way you normally would with WordPress's gallery tool. Then, in code mode, locate the gallery shortcode in the content and change the name of the shortcode to 'my_gallery' instead of 'gallery'. You can also specify `size : small, medium, or large` in the attributes as well as `columns` for the grid layout.

## Note

This is not a particularly compicated or feature-rich plugin. The same thing could most likely be done with hooks to add the auto-alt text feature to the WordPress gallery shortcode - which I will probably do as time permits. Feel free to ping me with any questions. Best way is through my site [atomiclotus.net](https://atomiclotus.net) or on Twitter @iconifyit
