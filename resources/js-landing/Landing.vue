<script setup>

import {onMounted} from "vue";

onMounted(() => {
    const smoothLinks = document.querySelectorAll(".smooth-link");
    smoothLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            let targetId = this.getAttribute("href").substring(1);
            let targetElement = document.getElementById(targetId);
            let navbarHeight = document.querySelector(".main-navbar").offsetHeight;
            let scrollToPosition = targetElement.offsetTop - (navbarHeight - 1);
            window.scrollTo({
                top: scrollToPosition,
                behavior: "smooth"
            });
        });
    });

    const dataBgs = document.querySelectorAll('[data-bg]')
    dataBgs.forEach((item) => {
        let bg = item.getAttribute('data-bg')
        item.style.backgroundImage = 'url(' + bg + ')'
        item.style.backgroundPosition = 'center'
        item.style.backgroundAttachment = 'fixed'
        item.style.backgroundSize = 'contain'
        item.style.backgroundRepeat = 'no-repeat'
        let div = document.createElement('div')
        div.classList.add('overlay')
        item.prepend(div)
    })

    window.addEventListener('scroll', function () {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        let heroHeight = document.querySelector('.hero').getBoundingClientRect().height;
        let navbar = document.querySelector('.main-navbar');
        if (scrollTop > (heroHeight - 150)) {
            navbar.classList.add('bg-dark');
        } else {
            navbar.classList.remove('bg-dark');
        }
        let sections = document.querySelectorAll('section');
        sections.forEach(function (section) {
            let sectionTop = section.offsetTop;
            let navbarHeight = navbar.clientHeight;
            if (scrollTop >= (sectionTop - navbarHeight)) {
                let smoothLinks = document.querySelectorAll('.smooth-link');
                smoothLinks.forEach(function (link) {
                    link.parentNode.classList.remove('active');
                    if (link.getAttribute('href') === '#' + section.getAttribute('id')) {
                        link.parentNode.classList.add('active');
                    }
                });
            }
        });
    });
})
</script>
<template>
    <nav class="navbar navbar-expand-lg main-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <!--                <img alt="Logo" src="/logo-white.svg">-->
                <img alt="Logo" src="/logo-white.svg" style="height: 50px; width: 300px">
            </a>
            <button aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler" data-bs-target="#navbarNav" data-bs-toggle="collapse"
                    type="button">
                <span class="navbar-toggler-icon">
                    <i class="ion-navicon"></i>
                </span>
            </button>
            <div id="navbarNav" class="collapse navbar-collapse">
                <div class="me-auto"></div>
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link smooth-link" href="#hero">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link smooth-link" href="#features">Job-Sys's Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link smooth-link" href="#blog">Featured Companies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link smooth-link" href="#project">Project</a>
                    </li>
                </ul>
                <form class="form-inline">
                    <a class="btn smooth-link align-middle btn-primary" href="/app">Login</a>
                </form>
            </div>
        </div>
    </nav>
    <section id="hero" class="hero bg-overlay" data-bg="/logo-white.svg">
        <div class="text py-5">
            <p class="lead">Sorsogon State University</p>
            <h1>Placement Services Office: A place where career and employment matters</h1>
            <div class="cta">
                <a class="btn btn-primary" href="/app/signup">Sign up now</a>
                <div class="link">
                    <a href="/app/login">Already have an account? Click here</a>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="padding">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-4 col-sm-12">
                    <div class="list-item">
                        <div class="icon">
                            <i class="ion-android-contact"></i>
                        </div>
                        <div class="desc">
                            <h2>Build Yourself</h2>
                            <p>Build your personal identity online and be connected with lots of opportunities</p>
                            <a class="more" href="#">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-sm-12">
                    <div class="list-item">
                        <div class="icon">
                            <i class="ion-ios-search-strong"></i>
                        </div>
                        <div class="desc">
                            <h2>Search a Job</h2>
                            <p>Make yourself free and look for a job that match with your skills</p>
                            <a class="more" href="#">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-sm-12">
                    <div class="list-item no-spacing">
                        <div class="icon">
                            <i class="ion-email-unread"></i>
                        </div>
                        <div class="desc">
                            <h2>Be Updated</h2>
                            <p>Subscribe and notify with the latest job opportunities</p>
                            <a class="more" href="#">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="blog" class="padding bg-grey">
        <div class="container">
            <h2 class="section-title">Featured Companies</h2>
            <p class="section-lead text-muted">Learn and gain some insight on what the company offers</p>
            <div class="section-body">
                <div class="row col-spacing">
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card">
                            <img alt="Article Image" class="card-img-top" src="/logo.svg">
                            <div class="card-body">
                                <div class="card-subtitle mb-2 text-muted">by <a href="#">John Doe</a> on August 10, 2017</div>
                                <h4 class="card-title"><a data-id="1" data-toggle="read" href="#">Duis aute irure dolor in reprehenderit in voluptate</a></h4>
                                <p class="card-text">Mauris eu eros in metus elementum porta eget sed ligula. Praesent consequat, ipsum molestie pellentesque venenatis.</p>
                                <div class="text-right">
                                    <a class="card-more" data-id="1" data-toggle="read" href="#">Read More <i class="ion-ios-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card">
                            <img alt="Article Image" class="card-img-top" src="/logo.svg">
                            <div class="card-body">
                                <div class="card-subtitle mb-2 text-muted">by <a href="#">John Doe</a> on August 10, 2017</div>
                                <h4 class="card-title"><a data-id="1" data-toggle="read" href="#">Velit esse cillum dolore eu blos siur dropsida wedor</a></h4>
                                <p class="card-text">Mauris eu eros in metus elementum porta eget sed ligula. Praesent consequat, ipsum molestie pellentesque venenatis.</p>
                                <div class="text-right">
                                    <a class="card-more" data-id="1" data-toggle="read" href="#">Read More <i class="ion-ios-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card">
                            <img alt="Article Image" class="card-img-top" src="/logo.svg">
                            <div class="card-body">
                                <div class="card-subtitle mb-2 text-muted">by <a href="#">John Doe</a> on August 10, 2017</div>
                                <h4 class="card-title"><a data-id="1" data-toggle="read" href="#">Excepteur sint as occaecat dros cupidatat non proident los</a></h4>
                                <p class="card-text">Mauris eu eros in metus elementum porta eget sed ligula. Praesent consequat, ipsum molestie pellentesque venenatis.</p>
                                <div class="text-right">
                                    <a class="card-more" data-id="1" data-toggle="read" href="#">Read More <i class="ion-ios-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card">
                            <img alt="Article Image" class="card-img-top" src="/logo.svg">
                            <div class="card-body">
                                <div class="card-subtitle mb-2 text-muted">by <a href="#">John Doe</a> on August 10, 2017</div>
                                <h4 class="card-title"><a data-id="1" data-toggle="read" href="#">Sunt in ado culpa qui officia deserunt mollit anim id</a></h4>
                                <p class="card-text">Mauris eu eros in metus elementum porta eget sed ligula. Praesent consequat, ipsum molestie pellentesque venenatis.</p>
                                <div class="text-right">
                                    <a class="card-more" data-id="1" data-toggle="read" href="#">Read More <i class="ion-ios-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card">
                            <img alt="Article Image" class="card-img-top" src="/logo.svg">
                            <div class="card-body">
                                <div class="card-subtitle mb-2 text-muted">by <a href="#">John Doe</a> on August 10, 2017</div>
                                <h4 class="card-title"><a data-id="1" data-toggle="read" href="#">Incididunt ut labore et labore dolore magna aliqua lorem</a></h4>
                                <p class="card-text">Mauris eu eros in metus elementum porta eget sed ligula. Praesent consequat, ipsum molestie pellentesque venenatis.</p>
                                <div class="text-right">
                                    <a class="card-more" data-id="1" data-toggle="read" href="#">Read More <i class="ion-ios-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card">
                            <img alt="Article Image" class="card-img-top" src="/logo.svg">
                            <div class="card-body">
                                <div class="card-subtitle mb-2 text-muted">by <a href="#">John Doe</a> on August 10, 2017</div>
                                <h4 class="card-title"><a data-id="1" data-toggle="read" href="#">Ut enim ad minim veniam quis nostrud enim ad kruv</a></h4>
                                <p class="card-text">Mauris eu eros in metus elementum porta eget sed ligula. Praesent consequat, ipsum molestie pellentesque venenatis.</p>
                                <div class="text-right">
                                    <a class="card-more" data-id="1" data-toggle="read" href="#">Read More <i class="ion-ios-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <div class="row align-items-center mt-5">
                <div class="col-12 col-md-6">
                    <h2>Don't miss our article</h2>
                    <p class="text-muted">Just enter your email then we will send an email about the latest articles</p>
                </div>
                <div class="col-12 col-md-6">
                    <form action="#" class="subscribe">
                        <input class="form-control" name="email" placeholder="Your email" type="email">
                        <button class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section id="project" class="bg-overlay padding" data-bg="/logo.svg">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-12 col-md-6">
                    <figure class="projects-picture">
                        <img alt="Youzhang" src="/logo-white.svg">
                    </figure>
                </div>
                <div class="col-12 col-md-6">
                    <div class="projects-details">
                        <div class="projects-badge">
                            Featured
                        </div>
                        <h2 class="projects-title">Featured Company</h2>
                        <p class="projects-description">
                            Featured company description
                        </p>
                        <div class="projects-cta">
                            <a class="btn btn-primary" href="https://codecanyon.net/item/youzhang-ionic-3-catalogue-template/20466954?ref=frameborder" target="_blank">
                                View
                            </a>
                            <a class="btn btn-link" href="https://codecanyon.net/user/frameborder?ref=frameborder" target="_blank">
                                More Companies
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="padding bg-grey">
        <div class="container">
            <h2 class="section-title text-center">Contact Us</h2>
            <p class="section-lead text-center text-muted">Send us your inquiry, we will help you and reply as soon as possible</p>
            <div class="section-body">
                <div class="row col-spacing">
                    <div class="col-12 col-md-5">
                        <p class="contact-text">You can send us something like a question or project, we are open 09:00 AM to 05:00 PM.</p>
                        <ul class="contact-icon">
                            <li><i class="ion ion-ios-telephone"></i>
                                <div>+639123456789</div>
                            </li>
                            <li><i class="ion ion-ios-email"></i>
                                <div>cict@sorsu.edu.ph</div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-12 col-md-7">
                        <form id="contact-form" class="contact row">
                            <div class="form-group col-6">
                                <input class="form-control" name="name" placeholder="Name" required="" type="text">
                            </div>
                            <div class="form-group col-6">
                                <input class="form-control" name="email" placeholder="Email" required="" type="email">
                            </div>
                            <div class="form-group col-12">
                                <input class="form-control" name="subject" placeholder="Subject" required="" type="text">
                            </div>
                            <div class="form-group col-12">
                                <textarea class="form-control" name="message" placeholder="Message" required=""></textarea>
                            </div>
                            <div class="form-group col-12 mt-2">
                                <button class="btn btn-primary">
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="callout">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-8 text">
                    <h3>Are you an employer?</h3>
                </div>
                <div class="col-12 col-md-4 cta">
                    <a class="btn btn-outline-primary" href="#">
                        Look for candidates here
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <figure class="d-flex justify-content-center">
                <img alt="Logo" src="/logo-white.svg" style="width: 200px">
            </figure>
            <p>
                Copyright &copy; 2023 Sorsogon State University
            </p>
            <p>
                Made with <i class="ion-heart"></i> By CICT
            </p>
        </div>
    </footer>
</template>
