<!-- Reporting Section -->
<div class="reporting-section">
    <!-- Intro headline/body (from Cronkite section) -->
    <div class="strategy-intro">
        <div class="strategy-intro-headline">
            <span class="display-headline">Reporting</span>
        </div>
        <div class="strategy-intro-body">
            <h3>I’m currently pursuing an M.A. in Investigative Journalism at the Walter Cronkite School in Phoenix, where I am also a <a href="https://cronkite.asu.edu/specializations/business-journalism-fellowship-graduate-fellowship/">Steele Fellow</a> in Investigative Business Journalism</h3>
        </div>
    </div>

    <!-- Features grid -->
    <?php echo do_shortcode('[reuben_features]'); ?>

    <!-- Cronkite grid -->
    <section class="content-section">
        <div class="section-container">
            <div class="stories-content">
                <?php echo do_shortcode('[reuben_dynamic_stories category="cronkite" layout="2-wide" limit="4" show_view_all="false" show_excerpt="true"]'); ?>
            </div>
        </div>
    </section>
</div>
