<style>
        :root {
            --primary-red: #e50914;
            --primary-dark: #141414;
            --primary-black: #000000;
            --primary-gray: #2c2c2c;
            --accent-gold: #ffd700;
            --accent-silver: #c0c0c0;
            --text-light: #f5f5f1;
            --text-gray: #b3b3b3;
            --gradient-dark: linear-gradient(180deg, #000000 0%, #141414 100%);
            --gradient-red: linear-gradient(135deg, #e50914 0%, #b81d24 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-red: #d32f2f;
            --accent-gold: #ffc107;
            --text-light: #f8f9fa;
            --text-gray: #adb5bd;
            --card-bg: #1e1e1e;
            --gradient-red: linear-gradient(135deg, #d32f2f 0%, #9a0007 100%);
            --gradient-gold: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 15px 40px rgba(211, 47, 47, 0.2);
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--primary-dark);
            color: var(--text-light);
            overflow-x: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--secondary-dark);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--accent-red);
            border-radius: 5px;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            font-weight: 600;
            background: var(--gradient-red);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            margin: 0 10px;
            padding: 8px 16px !important;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--gradient-red);
            color: white !important;
            transform: translateY(-2px);
        }
        
        /* Hero Section */
        .hero-section {
            position: relative;
            padding: 180px 0 120px;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.9)), 
                        url('https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1925&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-title {
            font-family: 'Oswald', sans-serif;
            font-size: 4rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--accent-gold), var(--accent-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .btn-hero {
            background: var(--gradient-red);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-hero:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Stats Section */
        .stats-section {
            padding: 60px 0;
            background: var(--secondary-dark);
            position: relative;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Theatre Cards */
        .theatre-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .theatre-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-red);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .theatre-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
            border-color: var(--accent-red);
        }
        
        .theatre-card:hover::before {
            transform: scaleX(1);
        }
        
        .theatre-image {
            height: 200px;
            background: linear-gradient(45deg, #2c3e50, #4a6491);
            position: relative;
            overflow: hidden;
        }
        
        .theatre-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
        }
        
        .theatre-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gradient-red);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .theatre-header {
            padding: 25px 25px 0;
        }
        
        .theatre-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 10px;
        }
        
        .theatre-location {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .theatre-body {
            padding: 0 25px 25px;
        }
        
        .theatre-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--accent-gold);
        }
        
        .facilities-container {
            margin: 20px 0;
        }
        
        .facility-tag {
            display: inline-block;
            background: rgba(255, 193, 7, 0.1);
            color: var(--accent-gold);
            padding: 6px 15px;
            margin: 0 8px 8px 0;
            border-radius: 20px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        
        .theatre-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-theatre {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-book {
            background: var(--gradient-red);
            color: white;
            border: none;
        }
        
        .btn-details {
            background: transparent;
            color: var(--accent-gold);
            border: 2px solid var(--accent-gold);
        }
        
        .btn-book:hover, .btn-details:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Filter Section */
        .filter-section {
            background: var(--secondary-dark);
            padding: 40px;
            border-radius: 15px;
            margin: -60px auto 50px;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 1200px;
        }
        
        .filter-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            color: var(--accent-gold);
            margin-bottom: 25px;
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 15px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-red);
            box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.25);
        }
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-gray);
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(to bottom, var(--secondary-dark), var(--primary-dark));
            padding: 80px 0 30px;
            margin-top: 100px;
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-red);
        }
        
        .footer-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 25px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--text-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .footer-links a:hover {
            color: var(--accent-red);
            padding-left: 10px;
        }
        
        .footer-links a::before {
            content: '▶';
            margin-right: 10px;
            font-size: 0.8rem;
            color: var(--accent-red);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover::before {
            opacity: 1;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: var(--gradient-red);
            transform: translateY(-5px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-gray);
            font-size: 0.9rem;
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .filter-section {
                margin: -40px 20px 50px;
                padding: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-section {
                padding: 150px 0 80px;
            }
            
            .theatre-card {
                margin-bottom: 20px;
            }
            
            .theatre-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: var(--gradient-dark);
            color: var(--text-light);
            overflow-x: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: var(--primary-dark);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary-red);
            border-radius: 5px;
        }
        
        /* Cinematic Text Effect */
        .cinematic-text {
            text-shadow: 0 0 10px rgba(229, 9, 20, 0.5),
                         0 0 20px rgba(229, 9, 20, 0.3),
                         0 0 30px rgba(229, 9, 20, 0.2);
            letter-spacing: 2px;
        }
        
        /* Hero Section */
        .hero-about {
            position: relative;
            height: 70vh;
            min-height: 500px;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)),
                        url('https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        
        .hero-about::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at center, transparent 30%, #000 70%);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-title {
            font-size: 4.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: var(--gradient-red);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-transform: uppercase;
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from {
                text-shadow: 0 0 10px rgba(229, 9, 20, 0.2),
                             0 0 20px rgba(229, 9, 20, 0.2),
                             0 0 30px rgba(229, 9, 20, 0.2);
            }
            to {
                text-shadow: 0 0 20px rgba(229, 9, 20, 0.4),
                             0 0 30px rgba(229, 9, 20, 0.4),
                             0 0 40px rgba(229, 9, 20, 0.4);
            }
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            color: var(--text-light);
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        /* Floating Cards */
        .floating-card {
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(229, 9, 20, 0.2);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .floating-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(229, 9, 20, 0.2);
        }
        
        .floating-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-red);
        }
        
        .card-icon {
            font-size: 3rem;
            color: var(--primary-red);
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }
        
        .card-icon::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-red);
        }
        
        /* Story Timeline */
        .timeline-container {
            position: relative;
            padding: 50px 0;
        }
        
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 3px;
            height: 100%;
            background: var(--gradient-red);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 60px;
            width: 45%;
        }
        
        .timeline-item:nth-child(odd) {
            left: 0;
            text-align: right;
            padding-right: 60px;
        }
        
        .timeline-item:nth-child(even) {
            left: 55%;
            padding-left: 60px;
        }
        
        .timeline-year {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--primary-red);
            margin-bottom: 10px;
        }
        
        .timeline-content {
            background: rgba(229, 9, 20, 0.1);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid var(--primary-red);
        }
        
        .timeline-item:nth-child(odd) .timeline-content {
            border-left: none;
            border-right: 4px solid var(--primary-red);
        }
        
        .timeline-dot {
            position: absolute;
            width: 20px;
            height: 20px;
            background: var(--primary-red);
            border-radius: 50%;
            top: 0;
            right: -11px;
            box-shadow: 0 0 0 5px rgba(229, 9, 20, 0.2);
        }
        
        .timeline-item:nth-child(even) .timeline-dot {
            left: -9px;
            right: auto;
        }
        
        /* Team Section */
        .team-card {
            background: rgba(20, 20, 20, 0.9);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            position: relative;
            margin-bottom: 30px;
        }
        
        .team-card:hover {
            transform: translateY(-15px);
            border-color: var(--primary-red);
            box-shadow: 0 15px 35px rgba(229, 9, 20, 0.2);
        }
        
        .team-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            filter: grayscale(20%);
            transition: filter 0.3s ease;
        }
        
        .team-card:hover .team-img {
            filter: grayscale(0%);
        }
        
        .team-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 100%);
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .team-card:hover .team-overlay {
            transform: translateY(0);
        }
        
        /* Stats Counter */
        .stat-box {
            text-align: center;
            padding: 40px 20px;
            background: rgba(229, 9, 20, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(229, 9, 20, 0.3);
            transition: all 0.3s ease;
        }
        
        .stat-box:hover {
            background: rgba(229, 9, 20, 0.2);
            border-color: var(--primary-red);
            transform: scale(1.05);
        }
        
        .stat-number {
            font-size: 4rem;
            font-weight: 900;
            color: var(--primary-red);
            margin-bottom: 10px;
            font-family: 'Arial Black', sans-serif;
        }
        
        .stat-label {
            font-size: 1.2rem;
            color: var(--text-light);
            opacity: 0.9;
        }
        
        /* Facilities Grid */
        .facility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .facility-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .facility-item:hover {
            background: rgba(229, 9, 20, 0.1);
            border-color: var(--primary-red);
            transform: translateY(-10px);
        }
        
        .facility-icon {
            font-size: 3rem;
            color: var(--primary-red);
            margin-bottom: 20px;
        }
        
        /* Section Titles */
        .section-title {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 3rem;
            font-weight: 900;
            display: inline-block;
            padding-bottom: 20px;
            position: relative;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-red);
        }
        
        /* Movie Reel Animation */
        .movie-reel {
            position: relative;
            padding: 80px 0;
            overflow: hidden;
        }
        
        .movie-reel::before {
            content: '🎬🎥🎞️📽️🎬🎥🎞️📽️';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            font-size: 3rem;
            white-space: nowrap;
            animation: scrollReel 20s linear infinite;
        }
        
        @keyframes scrollReel {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3.5rem;
            }
            
            .timeline-container::before {
                left: 30px;
            }
            
            .timeline-item {
                width: 100%;
                left: 0 !important;
                padding-left: 70px !important;
                padding-right: 20px !important;
                text-align: left !important;
            }
            
            .timeline-item:nth-child(odd) .timeline-content {
                border-right: none;
                border-left: 4px solid var(--primary-red);
            }
            
            .timeline-dot {
                left: 21px !important;
                right: auto !important;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .section-title h2 {
                font-size: 2.5rem;
            }
            
            .floating-card {
                padding: 30px 20px;
            }
        }
        
        /* Particle Background */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: var(--primary-red);
            border-radius: 50%;
            opacity: 0.3;
            animation: float 3s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
<nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-film me-2"></i>CINEMAKRISH
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="about.php">About</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="movies.php">Movies</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link active" href="my_bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="background: var(--gradient-gold);">
                            <i class=""></i> welcome
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>