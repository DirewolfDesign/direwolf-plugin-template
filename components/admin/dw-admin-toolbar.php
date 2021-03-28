<div class="dw-admin-toolbar">
    <h2>
        <img src="<?php echo $logo_url; // phpcs:ignore ?>" width="20" height="20">
        <?php echo esc_html__( '{{plugin_name}}', '{{plugin_text_domain}}' ); ?>
    </h2>
    <nav class="dw-admin-tabs">
    <?php foreach ( $tabs as $tab ) :
        printf(
            '<a class="dw-admin-toolbar-tab%s" href="%s">%s</a>',
            ! empty( $tab['is_active'] ) ? ' is-active' : '',
            esc_url( $tab['url'] ),
            // phpcs:ignore
            $tab['text']
        );
    endforeach; ?>
    </nav>
</div>
