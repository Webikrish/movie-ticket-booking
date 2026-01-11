 <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">CinemaKrish</div>
                    <p class="text-gray">Experience the magic of movies like never before. State-of-the-art technology, premium comfort, and unforgettable moments await you.</p>
                    
                    <!-- Dynamic Contact Information in Footer -->
                    <div class="mt-4">
                        <?php if(count($address_info) > 0): ?>
                            <p class="text-gray mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                <?php echo htmlspecialchars($address_info[0]['info_value']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if(count($phone_info) > 0): ?>
                            <p class="text-gray mb-2">
                                <i class="fas fa-phone me-2 text-danger"></i>
                                <?php echo htmlspecialchars($phone_info[0]['info_value']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if(count($email_info) > 0): ?>
                            <p class="text-gray mb-4">
                                <i class="fas fa-envelope me-2 text-danger"></i>
                                <?php echo htmlspecialchars($email_info[0]['info_value']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Social Media Links (Dynamic from Database) -->
                    <div class="social-icons mt-4">
                        <?php foreach($social_links as $social): ?>
                            <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank" title="<?php echo htmlspecialchars($social['platform']); ?>">
                                <i class="<?php echo $social['icon_class']; ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Quick Links (Dynamic from Database) -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="footer-links">
                        <?php foreach($footer_links as $link): ?>
                            <?php if($link['display_order'] <= 5): ?>
                                <li><a href="<?php echo htmlspecialchars($link['url']); ?>"><?php echo htmlspecialchars($link['title']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Information Links (Dynamic from Database) -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-4">Information</h5>
                    <ul class="footer-links">
                        <?php foreach($footer_links as $link): ?>
                            <?php if($link['display_order'] > 5): ?>
                                <li><a href="<?php echo htmlspecialchars($link['url']); ?>"><?php echo htmlspecialchars($link['title']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Newsletter Subscription -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-4">Newsletter</h5>
                    <p class="text-gray">Subscribe to get updates on new movies and special offers.</p>
                    <form id="newsletterForm" method="POST" action="subscribe.php">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            <button class="btn btn-hero" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <small class="text-gray">We respect your privacy. Unsubscribe at any time.</small>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> CinemaKrish. All Rights Reserved.</p>
                <p class="text-gray small">Powered by MovieMagic Systems</p>
            </div>
        </div>
    </footer>