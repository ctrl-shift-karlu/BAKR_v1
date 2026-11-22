{{-- PLACEHOLDER: replace each card with a real, consented graduate story. Do not publish invented quotes. --}}
<section class="nx-section bk-stories" id="stories" aria-labelledby="storiesTitle">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="nx-h2" id="storiesTitle">Scholars who made it</h2>
            <p class="nx-lead mx-auto">Real stories from DNSC scholarship graduates will appear here, shared with their permission, so you can learn from people who walked the path before you.</p>
        </div>
        <div class="row g-4">
            @foreach ($blocks['stories'] as $i => $story)
                <div class="col-md-4">
                    <article class="bk-story-card">
                        <figure class="bk-photo-slot bk-ratio-sq bk-slot-round" data-photo="{{ asset($story['photo']) }}">
                            <div class="bk-slot-empty"><i class="bi bi-person-fill" aria-hidden="true"></i></div>
                        </figure>
                        <i class="bi bi-quote bk-quote-mark" aria-hidden="true"></i>
                        <p class="bk-tbd">Testimonial to be added after the graduate gives permission.</p>
                        <div class="bk-story-meta"><strong class="bk-tbd">Graduate name</strong><span class="bk-tbd">Program, batch, scholarship</span></div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
