set -e
for d in /var/www/antonio-casolino.1wp.site "$HOME/antonio-casolino.1wp.site" "$HOME"; do [ -f "$d/wp-config.php" ] && cd "$d" && break; done
echo "dir: $(pwd)"
mkdir -p wp-content/mu-plugins
curl -fsSL https://raw.githubusercontent.com/a-casolino/acasolino.com/wordpress/mu-plugins/acasolino-progetti.php -o wp-content/mu-plugins/acasolino-progetti.php
wp language core install it_IT --activate
wp option update timezone_string Europe/Rome
wp option update date_format 'j F Y'
wp option update blogdescription 'Web project manager e visual designer'
wp plugin delete hello akismet || true
wp post delete 1 2 3 --force || true
wp plugin install seo-by-rank-math --activate
wp rewrite structure '/%postname%/'
HOME_ID=$(wp post create --post_type=page --post_status=publish --post_title=Home --porcelain)
BLOG_ID=$(wp post create --post_type=page --post_status=publish --post_title=Blog --post_name=blog --porcelain)
wp post create --post_type=page --post_status=publish --post_title='Chi sono' --post_name=about --porcelain
wp post create --post_type=page --post_status=publish --post_title=Contatti --post_name=contatti --porcelain
wp option update show_on_front page
wp option update page_on_front $HOME_ID
wp option update page_for_posts $BLOG_ID
wp term update category 1 --name=Blog --slug=blog || true
wp rewrite flush --hard
wp post list --post_type=page --fields=ID,post_title,post_name
echo DONE
