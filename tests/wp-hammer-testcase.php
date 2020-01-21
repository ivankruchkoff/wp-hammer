<?php

/**
 * Base unit test class for Co-Authors Plus
 */

class WP_HammerTestCase extends WP_UnitTestCase {

	protected $suppress = false;

	public function setUp() {
		global $wpdb;
		parent::setUp();
		$this->suppress = $wpdb->suppress_errors();

		$_SERVER['REMOTE_ADDR'] = '';

		$this->author1 = $this->factory->user->create( array( 'role' => 'author', 'user_login' => 'author1' ) );
		$this->editor1 = $this->factory->user->create( array( 'role' => 'editor', 'user_login' => 'editor2' ) );

		$post = array(
			'post_author'     => $this->author1,
			'post_status'     => 'publish',
			'post_content'    => rand_str(),
			'post_title'      => rand_str(),
			'post_type'       => 'post',
		);

		$this->author1_post1 = wp_insert_post( $post );

		$post = array(
			'post_author'     => $this->author1,
			'post_status'     => 'publish',
			'post_content'    => rand_str(),
			'post_title'      => rand_str(),
			'post_type'       => 'post',
		);

		$this->author1_post2 = wp_insert_post( $post );

		$page = array(
			'post_author'     => $this->author1,
			'post_status'     => 'publish',
			'post_content'    => rand_str(),
			'post_title'      => rand_str(),
			'post_type'       => 'page',
		);

		$this->author1_page1 = wp_insert_post( $page );

		$page = array(
			'post_author'     => $this->author1,
			'post_status'     => 'publish',
			'post_content'    => rand_str(),
			'post_title'      => rand_str(),
			'post_type'       => 'page',
		);

		$this->author1_page2 = wp_insert_post( $page );

		$comment = array(
			'comment_post_ID'      => $this->author1_post1,
			'comment_author_email' => 'commenter_1@example.org',
		);

		$this->comment_1 = $this->factory()->comment->create( $comment );

		$comment = array(
			'comment_post_ID'      => $this->author1_post2,
			'comment_author_email' => 'commenter_2@example.org',
		);

		$this->comment_2 = $this->factory()->comment->create( $comment );

		update_option( 'secure_auth_key', '8d6rJj)GY2wH>7XfZV3D3+h,26em4u{n^fhkyzJA+Lpwfqy2VBQ+ozPnxGqD>M}q' );
		update_option( 'cloudflare_api_key', 'jyj=jBY6xc}Mh,FLa6AczhnUKp=tsK9d78{9iE}6Px6ptV9uK?CcH2#CkJ.*bpbE' );
		set_transient( 'secret_transient', 'secret', DAY_IN_SECONDS );

		$session_tokens = array(
			'VfbeB3emgckN4DRpoU6tEg8U8KvTEPkZZqQmz38NV4nFhZokyRCbKD' => array(
				'expiration' => time() + DAY_IN_SECONDS,
				'ip'         => '127.0.0.1',
				'login'      => time() - HOUR_IN_SECONDS,
			),
		);

		update_user_meta( $this->author1, 'session_tokens', $session_tokens );
		update_user_meta( $this->author1, 'googleauthenticator_secret', 'B&8hD{jV2ZBR8G4$rVc%' );

		$args = array(
				"-l",
				"users=5,posts=100.post_date",
				"-f",
				"posts.post_author=auto,users.user_pass=auto,users.user_email=ivan+__ID__@kruchkoff.com,posts.post_title=ipsum,comments.comment_author_email=ivan+__comment_ID__@kruchkoff.com",
				"-s",
				"options,usermeta"
		);
		$assoc_args = array();
		$this->settings = new WP_CLI\Hammer\Settings();
		$this->settings->parse_arguments( $args, $assoc_args );
	}

	public function tearDown() {
		global $wpdb;
		parent::tearDown();
		$wpdb->suppress_errors( $this->suppress );
	}
}
