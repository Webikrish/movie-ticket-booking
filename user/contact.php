<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CinemaKrish</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-red: #e50914;
            --primary-dark: #0f0f0f;
            --primary-light: #f8f9fa;
            --secondary-gray: #6c757d;
            --accent-gold: #ffd700;
            --shadow-light: rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--primary-light);
            color: #333;
            line-height: 1.6;
        }
        
        /* Header Section */
        .page-header {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                        url('https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0 40px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }
        
        .page-header p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        /* Contact Info Cards */
        .contact-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            border-top: 4px solid var(--primary-red);
            text-align: center;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .contact-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-red);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
        }
        
        .contact-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .contact-card p {
            color: var(--secondary-gray);
            margin-bottom: 10px;
        }
        
        /* Contact Form */
        .contact-form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }
        
        .form-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-red);
            display: inline-block;
        }
        
        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(229, 9, 20, 0.25);
        }
        
        .btn-submit {
            background: var(--primary-red);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-submit:hover {
            background: #b81d24;
            transform: translateY(-2px);
        }
        
        /* Map Section */
        .map-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }
        
        .map-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        /* Business Hours */
        .hours-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .hours-table {
            width: 100%;
        }
        
        .hours-table td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .hours-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #333;
        }
        
        /* FAQ Section */
        .faq-item {
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .faq-question {
            background: #f8f9fa;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: #e9ecef;
            border-left-color: var(--primary-red);
        }
        
        .faq-question.active {
            background: #e9ecef;
            border-left-color: var(--primary-red);
        }
        
        .faq-answer {
            padding: 20px;
            background: white;
            border-left: 4px solid var(--primary-red);
            display: none;
        }
        
        .faq-answer.show {
            display: block;
        }
        
        /* Social Media */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #f8f9fa;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .social-icon:hover {
            background: var(--primary-red);
            color: white;
            transform: translateY(-3px);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2.2rem;
            }
            
            .page-header {
                padding: 60px 0 30px;
            }
            
            .contact-form-container {
                padding: 25px;
            }
            
            .contact-card {
                padding: 25px;
            }
            
            .contact-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .page-header p {
                font-size: 1rem;
            }
            
            .form-title {
                font-size: 1.3rem;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        /* Call to Action */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-red) 0%, #b81d24 100%);
            color: white;
            padding: 50px 0;
            text-align: center;
            margin-top: 40px;
        }
        
        .cta-section h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .btn-cta {
            background: white;
            color: var(--primary-red);
            border: none;
            padding: 12px 35px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-cta:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

    <?php include 'nav.php'?>
    <br><br>
    <!-- Header Section -->
    <header class="page-header">
        <div class="container">
            <h1 class="animate-fade">Contact Us</h1>
            <p class="animate-fade" style="animation-delay: 0.2s">We're always here to help. Get in touch with us for any questions or assistance.</p>
        </div>
    </header>
    
    <!-- Contact Information -->
    <section class="container py-5">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-card animate-fade" style="animation-delay: 0.3s ;background: #0a0a0a;color:white">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Our Cinema</h3>
                    <p><strong>CinemaKrish Headquarters</strong></p>
                    <p>123 Movie Boulevard</p>
                    <p>Entertainment District</p>
                    <p>Mumbai, Maharashtra 400001</p>
                    <a href="#map" class="btn btn-outline-danger mt-3">View on Map</a>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-card animate-fade" style="animation-delay: 0.4s;background: #0a0a0a;color:white">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p><strong>Customer Support</strong></p>
                    <p>+91 22 1234 5678</p>
                    <p><strong>Ticket Booking</strong></p>
                    <p>+91 22 9876 5432</p>
                    <a href="tel:+912212345678" class="btn btn-danger mt-3">
                        <i class="fas fa-phone me-2"></i>Call Now
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="contact-card animate-fade" style="animation-delay: 0.5s;background: #0a0a0a;color:white">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p><strong>General Inquiries</strong></p>
                    <p>info@cinemakrish.com</p>
                    <p><strong>Corporate Events</strong></p>
                    <p>events@cinemakrish.com</p>
                    <a href="mailto:info@cinemakrish.com" class="btn btn-danger mt-3">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Form & Map -->
    <section class="container py-5" >
        <div class="row">
            <div class="col-lg-6 mb-5">
                <div class="contact-form-container animate-fade" style="animation-delay: 0.6s ;background: #0a0a0a">
                    <h3 class="form-title">Send Us a Message</h3>
                    <form id="contactForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" placeholder="Enter your name" required style='background: #0a0a0a;color:white'>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Email</label>
                                <input type="email" class="form-control" placeholder="Enter your email" required style='background: #0a0a0a;color:white'>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" placeholder="Enter your phone number" style='background: #0a0a0a;color:white'>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select" required style='background: #0a0a0a'>
                                <option value="" selected disabled>Select a subject</option>
                                <option>General Inquiry</option>
                                <option>Ticket Booking Issue</option>
                                <option>Corporate Event</option>
                                <option>Feedback</option>
                                <option>Complaint</option>
                                <option>Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="5" placeholder="Type your message here..." required  style='background: #0a0a0a;color:white'></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-6 mb-5">
                <div class="map-container animate-fade" style="animation-delay: 0.7s" id="map">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3771.0172861667595!2d72.82821431588728!3d19.064988787100122!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7c96a34dc4401%3A0x3ffc07e83942b13f!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1628684567890!5m2!1sen!2sin" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
                
                <div class="hours-card animate-fade" style="animation-delay: 0.8s ;background: #0a0a0a" >
                    <h4 class="mb-4">Business Hours</h4>
                    <table class="hours-table" >
                        <tr>
                            <td>Monday - Friday</td>
                            <td>9:00 AM - 11:00 PM</td>
                        </tr>
                        <tr>
                            <td>Saturday</td>
                            <td>9:00 AM - 12:00 AM</td>
                        </tr>
                        <tr>
                            <td>Sunday</td>
                            <td>9:00 AM - 11:00 PM</td>
                        </tr>
                        <tr>
                            <td>Public Holidays</td>
                            <td>10:00 AM - 11:00 PM</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section class="container py-5">
        <div class="row">
            <div class="col-lg-6 mb-5">
                <div class="hours-card animate-fade" style="animation-delay: 0.9s ;background: #0a0a0a">
                    <h4 class="mb-4">Frequently Asked Questions</h4>
                    <div class="faq-container" >
                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFAQ(this)" style='background: #0a0a0a'>
                                How can I book tickets online?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer" style='background: #0a0a0a'>
                                <p>You can book tickets through our website or mobile app. Select your movie, showtime, seats, and complete the payment securely.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" >
                            <div class="faq-question" onclick="toggleFAQ(this)" style='background: #0a0a0a'>
                                What is your cancellation policy?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer" style='background: #0a0a0a'>
                                <p>Tickets can be cancelled up to 2 hours before showtime. Refunds are processed within 5-7 business days to your original payment method.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFAQ(this)" style='background: #0a0a0a'>
                                Do you have wheelchair access?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer" style='background: #0a0a0a'>
                                <p>Yes, all our theaters are fully wheelchair accessible with dedicated seating areas and accessible restrooms.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleFAQ(this)" style='background: #0a0a0a'>
                                Can I bring outside food?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer" style='background: #0a0a0a'>
                                <p>For hygiene and safety reasons, outside food is not permitted. We offer a wide variety of snacks and beverages at our concession stands.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-5">
                <div class="hours-card animate-fade" style="animation-delay: 1s ;background: #0a0a0a">
                    <h4 class="mb-4">Connect With Us</h4>
                    <p class="mb-4">Stay updated with our latest movies, events, and special offers by following us on social media.</p>
                    
                    <div class="social-links">
                        <a href="#" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                    
                    <div class="mt-5">
                        <h5>Subscribe to Newsletter</h5>
                        <p>Get updates on new releases and special offers.</p>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Enter your email">
                            <button class="btn btn-danger" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready for an Amazing Movie Experience?</h2>
            <p>Book your tickets now and enjoy the best cinema experience in town.</p>
            <a href="index.php" class="btn-cta">Book Tickets Now</a>
        </div>
    </section>
    
    
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // FAQ Toggle Function
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('i');
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-question').forEach(q => {
                if (q !== element) {
                    q.classList.remove('active');
                    q.nextElementSibling.classList.remove('show');
                    q.querySelector('i').classList.remove('fa-chevron-up');
                    q.querySelector('i').classList.add('fa-chevron-down');
                }
            });
            
            // Toggle current FAQ item
            if (answer.classList.contains('show')) {
                answer.classList.remove('show');
                element.classList.remove('active');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                answer.classList.add('show');
                element.classList.add('active');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
        
        // Form Submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.disabled = true;
            
            // Simulate form submission (in real app, this would be AJAX)
            setTimeout(() => {
                // Show success message
                alert('Thank you! Your message has been sent successfully. We will get back to you soon.');
                
                // Reset form and button
                this.reset();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
        
        // Smooth scroll to map
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Animate elements on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.animate-fade');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });
            
            animateElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>