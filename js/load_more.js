const WPLoadMorePosts = (() => {
  const config = {
    api: load_more_script.rest_api_url,
    startPage: 0,
    postsPerPage: load_more_script.posts_per_page,
	limit: load_more_script.post_limit,
	post_container: load_more_script.post_container,
	post_content: load_more_script.post_content,
	thumbnails_default: load_more_script.thumbnails_default,
	loading_style: 'loading',
  };
  let postsLoaded = false;
  let postsContent = document.querySelector(config.post_container);
  let btnLoadMore =  document.getElementById('wp-load-more');
  if ( typeof( btnLoadMore ) == 'undefined' || btnLoadMore == null ) {
	  return;
  }
  const loadContent = function() {
      // Increase every time content is loaded
      ++config.startPage;
      const params = {
        _embed: true,
        page: config.startPage,
		load_more: true,
        per_page: config.postsPerPage,
		limit: config.limit,
      }
      // Builds the API URL with args.
      const getApiUrl = (url) => {
        let apiUrl = new URL(url);
        apiUrl.search = new URLSearchParams(params).toString();
        return apiUrl;
      };
      // Make a request to the REST API URL.
      const loadPosts = async () => {
		btnLoadMore.classList.add( config.loading_style );
        const url = getApiUrl(config.api);
        const request = await fetch(url);
        const posts = await request.json();
		const rest_total_pages = request.headers.get('X-WP-TotalPages');
        // Builds the HTML to show the posts
        const postsHtml = renderPostHtml( posts,rest_total_pages );
        // Adds the HTML into the selected div
        postsContent.innerHTML += postsHtml;
        // Required for the infinite scroll
        postsLoaded = true;
		btnLoadMore.classList.remove( config.loading_style );
      };
      // Builds the HTML to show all posts
      const renderPostHtml = ( posts, rest_total_pages ) => {
        let postHtml = '';
		if ( ( typeof posts.data !== 'undefined' && typeof posts.data.status !== 'undefined' ) || config.startPage >= rest_total_pages  ) {
			btnLoadMore.style.visibility = "hidden";
		}
		for(let post of posts) {
			postHtml += postTemplate(post);
		};
        return postHtml;
      };
      // HTML template for a post
      const postTemplate = (post) => {
		  let newStr = config.post_content;
		  newStr = newStr.replace( '[post_url]', post.link );
		  newStr = newStr.replace( '[post_title]', post.title.rendered );
		  if ( typeof( post._embedded['wp:featuredmedia'] ) !== 'undefined' ) {
			  var thumbnails_default_img = post._embedded['wp:featuredmedia'][0].source_url;
			  var alt_text = post._embedded['wp:featuredmedia'][0].alt_text;
			  if ( alt_text == '' ) {
				  alt_text = post.title.rendered;
			  }
		  } else {
			 var thumbnails_default_img = config.thumbnails_default;
		  }
		  newStr = newStr.replace( '[post_attachment]', thumbnails_default_img );
		  newStr = newStr.replace( '[alt_text]',alt_text );
		  newStr = newStr.replace( '[post_excerpt]', post.excerpt.rendere );
          return newStr;
      };
      loadPosts();
  };
  // Public Properties and Methods
  return {
    wp_load_more_init: loadContent
  };

})();
// Initialize Infinite Scroll
WPLoadMorePosts.wp_load_more_init();