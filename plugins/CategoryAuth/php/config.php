<?php
class CategoryAuth extends MTPlugin {

    var $registry = array(
        'name' => 'CategoryAuth',
        'id'   => 'CategoryAuth',
        'key'  => 'categoryauth',
        'author_name' => 'Alfasado Inc.',
        'author_link' => 'http://alfasado.net/',
        'version' => '0.2',
        'callbacks' => array(
            'pre_build_page' => 'pre_build_page'
        ),
        'config_settings' => array(
            'CategoryAuthUserNameFieldBasename' => array( 'default' => 'categoryauth_username' ),
            'CategoryAuthPasswordFieldBasename' => array( 'default' => 'categoryauth_password' ), 
            'FolderAuthUserNameFieldBasename' => array( 'default' => 'folderauth_username' ),
            'FolderAuthPasswordFieldBasename' => array( 'default' => 'folderauth_password' ), 
        ),
    );

    function pre_build_page ( $mt, $ctx, &$args ) {
        $app = $ctx->stash( 'bootstrapper' );
        $fileinfo = $app->stash( 'fileinfo' );
        $archive_type = $fileinfo->archive_type;
        $categories = array();
        $entry_class;
        if ( ( $archive_type == 'Individual' ) || ( $archive_type == 'Page' )
             || ( $archive_type == 'Category' ) ) {
             if ( $archive_type == 'Category' ) {
                $category = $ctx->stash( 'category' );
                array_push( $categories, $category );
             } elseif ( $archive_type == 'Individual' ) {
                $entry = $ctx->stash( 'entry' );
                $categories = $entry->categories();
                $entry_class = 'entry';
             } elseif ( $archive_type == 'Page' ) {
                $entry = $ctx->stash( 'entry' );
                $categories = array_push( $categories, $entry->folder() );
                $entry_class = 'page';
             }
        }
        if ( $categories ) {
            $user_field = NULL;
            $pass_field = NULL;
            if ( $entry_class && $entry_class == 'page' ) {
                $user_field = $app->config( 'FolderAuthUserNameFieldBasename' );
                $pass_field = $app->config( 'FolderAuthPasswordFieldBasename' );
            } else {
                $user_field = $app->config( 'CategoryAuthUserNameFieldBasename' );
                $pass_field = $app->config( 'CategoryAuthPasswordFieldBasename' );
            }
            if ( (! $user_field ) || (! $user_field ) ) {
                return;
            }
            foreach ( $categories as $category ) {
                $username = $category->{ $category->_prefix . 'field.' . $user_field };
                $password = $category->{ $category->_prefix . 'field.' . $pass_field };
                if ( $username && $password ) {
                    if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) && ( $_SERVER[ 'PHP_AUTH_USER' ]
                            === $username && $_SERVER[ 'PHP_AUTH_PW' ]
                            === $password ) ) {
                    } else {
                        header( 'WWW-Authenticate: Basic realm=""' );
                        header( 'HTTP/1.0 401 Unauthorized' );
                        exit();
                    }
                }
            }
        }
    }
}

?>