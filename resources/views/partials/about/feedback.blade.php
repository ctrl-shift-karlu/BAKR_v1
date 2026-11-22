{{-- Feedback about BAKR (owner, 2026-11-21): a 1-5 star rating, the parts of BAKR used, an optional comment.
     Saved as a contact_messages row (topic feedback) and e-mailed to the BAKR team (ContactMessageService);
     the admin reads it on Admin › Messages. The summary on the right counts ONLY feedback the sender agreed
     to share AND the BAKR team approved (ContactMessage::publicFeedback). Form styles: bakr-contact.css (.bk-msg-*). --}}
@php
    $me = auth()->user();
    $oldRating = (int) old('rating');
    $oldAspects = (array) old('aspects', []);
@endphp
<section class="bk-msg bk-rate-section" id="feedback" aria-labelledby="feedbackTitle">
    <div class="container bk-msg-grid">
        <div class="bk-msg-form">
            <h2 id="feedbackTitle">How was BAKR for you?</h2>
            <p class="bk-msg-intro">Rate your experience and tell us what helped or what got in the way. Your feedback goes straight to the BAKR team and shapes what we fix next.</p>

            @if (session('feedback_sent'))
                <div class="bk-msg-alert is-ok" role="status" tabindex="-1" id="feedbackSent">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <div><strong>Thank you for your feedback!</strong><span>We read every rating. If you wrote a comment, we may reply to the e-mail you gave us.</span></div>
                </div>
            @endif
            @if ($errors->any())
                <div class="bk-msg-alert is-error" role="alert" tabindex="-1" id="feedbackErrors">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                    <div><strong>Please check the highlighted fields.</strong></div>
                </div>
            @endif

            <form id="feedbackForm" method="POST" action="{{ route('about.feedback') }}" novalidate data-busy="Sending your feedback…">
                @csrf

                <fieldset class="bk-rate @error('rating') is-invalid @enderror">
                    <legend>Your rating</legend>
                    <div class="bk-rate-row">
                        <div class="bk-stars">
                            @foreach (\App\Models\ContactMessage::RATINGS as $n => $word)
                                <input type="radio" class="bk-star-input" id="fbStar{{ $n }}" name="rating" value="{{ $n }}" data-word="{{ $word }}" @checked($oldRating === $n) required>
                                <label class="bk-star" for="fbStar{{ $n }}" title="{{ $n }} – {{ $word }}">
                                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                                    <span class="visually-hidden">{{ $n }} {{ \Illuminate\Support\Str::plural('star', $n) }}: {{ $word }}</span>
                                </label>
                            @endforeach
                        </div>
                        <output class="bk-rate-word" id="fbRateWord" aria-live="polite" data-empty="Tap a star to rate">
                            {{ $oldRating ? $oldRating.' out of 5 · '.\App\Models\ContactMessage::RATINGS[$oldRating] : 'Tap a star to rate' }}
                        </output>
                    </div>
                    @error('rating') <div class="bk-field-error">{{ $message }}</div> @enderror
                </fieldset>

                <fieldset class="bk-topics bk-aspects">
                    <legend>Which parts of BAKR did you use? <span class="bk-opt">(optional, choose any)</span></legend>
                    @foreach (\App\Models\ContactMessage::ASPECTS as $value => $label)
                        <label class="bk-topic is-check">
                            <input type="checkbox" name="aspects[]" value="{{ $value }}" @checked(in_array($value, $oldAspects, true))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    @error('aspects') <div class="bk-field-error">{{ $message }}</div> @enderror
                    @error('aspects.*') <div class="bk-field-error">{{ $message }}</div> @enderror
                </fieldset>

                <div>
                    <label for="fbMessage">Tell us more <span class="bk-opt">(optional)</span></label>
                    <textarea id="fbMessage" name="message" rows="4" maxlength="2000" placeholder="What worked well? What should we improve?" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                    <div class="bk-msg-hint"><span>Please don't include passwords, ID numbers or grades.</span><span id="fbCount" aria-hidden="true">0 / 2000</span></div>
                    @error('message') <div class="bk-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="bk-msg-row">
                    <div>
                        <label for="fbName">Your name</label>
                        <input type="text" id="fbName" name="name" value="{{ old('name', $me?->name) }}" maxlength="80" autocomplete="name" required @error('name') aria-invalid="true" @enderror>
                        @error('name') <div class="bk-field-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="fbEmail">Your e-mail</label>
                        <input type="email" id="fbEmail" name="email" value="{{ old('email', $me?->email) }}" maxlength="120" autocomplete="email" required @error('email') aria-invalid="true" @enderror>
                        @error('email') <div class="bk-field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="bk-check">
                        <input type="checkbox" name="share_publicly" value="1" @checked(old('share_publicly'))>
                        <span>You may show my rating and comment on this site after the BAKR team approves it. Show only my first name, never my e-mail.</span>
                    </label>
                    <p class="bk-msg-hint"><span>We use your name and e-mail only to reply (<a href="{{ route('privacy') }}">Privacy Policy</a>).</span></p>
                </div>

                {{-- Honeypot: hidden from people, bots tend to fill it in. --}}
                <div class="bk-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <button type="submit" class="nx-btn nx-btn-gold" id="fbSubmit"><i class="bi bi-send" aria-hidden="true"></i> Send feedback</button>
            </form>
        </div>

        <aside class="bk-msg-side bk-rate-side" aria-labelledby="ratingsTitle">
            <h3 id="ratingsTitle">What others say</h3>
            @if ($ratings['count'] > 0)
                <div class="bk-rate-summary">
                    <strong class="bk-rate-avg">{{ number_format($ratings['average'], 1) }}</strong>
                    <div>
                        <div class="bk-rate-stars" role="img" aria-label="Average {{ number_format($ratings['average'], 1) }} out of 5 stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $ratings['average'] >= $i ? 'bi-star-fill' : ($ratings['average'] >= $i - 0.5 ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
                            @endfor
                        </div>
                        <small>From {{ $ratings['count'] }} shared {{ \Illuminate\Support\Str::plural('rating', $ratings['count']) }}</small>
                    </div>
                </div>
                <ul class="bk-rate-dist" aria-label="Ratings by number of stars">
                    @foreach ($ratings['stars'] as $star => $s)
                        <li>
                            <span>{{ $star }} <i class="bi bi-star-fill" aria-hidden="true"></i></span>
                            <span class="bk-rate-bar" aria-hidden="true"><span style="--w: {{ $s['percent'] }}%"></span></span>
                            <small>{{ $s['count'] }}</small>
                        </li>
                    @endforeach
                </ul>
                @foreach ($ratings['quotes'] as $q)
                    <blockquote class="bk-rate-quote">
                        <p>{{ \Illuminate\Support\Str::limit($q->message, 220) }}</p>
                        <footer>{{ \Illuminate\Support\Str::before($q->name, ' ') }} · {{ $q->rating }} / 5</footer>
                    </blockquote>
                @endforeach
            @else
                <p class="bk-rate-empty"><i class="bi bi-chat-heart" aria-hidden="true"></i> No shared ratings yet. Yours could be the first.</p>
            @endif
            <p class="bk-rate-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Only feedback that you agree to share, and that the BAKR team approves, appears here.</p>
        </aside>
    </div>
</section>
