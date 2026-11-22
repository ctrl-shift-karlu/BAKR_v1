{{-- Site footer. --}}
<footer class="bk-footer">
    <div class="container bk-footer-cta">
        <div>
            <h4>Your local scholarship starting point.</h4>
            <p>Built by the DNSC community, BAKR keeps your scholarship search close to home and connected to the official source.</p>
        </div>
        <a class="nx-btn nx-btn-green" href="{{ url('contact') }}"><i class="bi bi-envelope-fill" aria-hidden="true"></i> Contact Us</a>
    </div>

    <div class="container bk-footer-body">
        <div class="bk-footer-main">
            <div class="bk-footer-brandcol">
                <div class="bk-footer-brand">
                    <span class="brand-mark brand-badge" aria-hidden="true"><img src="{{ asset('images/logo/bakr-logo.png') }}" alt=""></span>
                    BAKR
                </div>
                <p class="bk-footer-copy">{{ config('bakr.tagline') }}, built by students to make your scholarship search clearer. You submit your final application through the DNSC Scholarship and Grants Unit.</p>
                <a class="bk-footer-location" href="https://www.google.com/maps/search/?api=1&query=Davao+del+Norte+State+College" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Locate us at Davao del Norte State College
                </a>
                @include('partials.pwa-install')
            </div>

            <nav aria-label="Explore">
                <h5>Explore</h5>
                <ul class="bk-footer-links">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ url('scholarships') }}">Scholarships</a></li>
                    <li><a href="{{ route('find') }}">Find a Scholarship</a></li>
                    <li><a href="{{ url('/') }}#faqs">FAQs</a></li>
                </ul>
            </nav>

            <nav aria-label="About BAKR">
                <h5>BAKR</h5>
                <ul class="bk-footer-links">
                    <li><a href="{{ url('about') }}">About Us</a></li>
                    <li><a href="{{ url('contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('student.register') }}">Create an Account</a></li>
                    <li><a href="{{ route('student.login') }}">Student Login</a></li>
                </ul>
            </nav>

            <div aria-label="Contact BAKR">
                <h5>Contact</h5>
                <div class="bk-footer-contact">
                    <a href="mailto:bakr.dnsc@gmail.com"><i class="bi bi-envelope-fill" aria-hidden="true"></i> bakr.dnsc@gmail.com</a>
                    <span><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Davao del Norte State College</span>
                </div>
            </div>
        </div>

        <div class="bk-footer-bottom">
            <span>&copy; {{ date('Y') }} BAKR. Academic Project.</span>
            <span>Davao del Norte State College.</span>
        </div>
    </div>
</footer>
