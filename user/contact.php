<!-- Contact Section (Dynamic from Database) -->
        <section id="contact" class="contact-section mb-5">
            <h2 class="section-title mb-4">Contact Us</h2>
            
            <div class="row">
                <div class="col-lg-6">
                    <form id="contactForm" method="POST" action="submit_contact.php">
                        <div class="mb-3">
                            <label class="form-label text-light">Your Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">Your Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">Subject</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">Your Message</label>
                            <textarea class="form-control" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-hero">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
                
                <div class="col-lg-6">
                    <div class="contact-info">
                        <!-- Address Information -->
                        <?php if(count($address_info) > 0): ?>
                            <?php foreach($address_info as $address): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="<?php echo $address['icon_class']; ?>"></i>
                                    </div>
                                    <div>
                                        <h5>Address</h5>
                                        <p class="text-gray"><?php echo htmlspecialchars($address['info_value']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Phone Information -->
                        <?php if(count($phone_info) > 0): ?>
                            <?php foreach($phone_info as $phone): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="<?php echo $phone['icon_class']; ?>"></i>
                                    </div>
                                    <div>
                                        <h5><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $phone['info_key']))); ?></h5>
                                        <p class="text-gray"><?php echo htmlspecialchars($phone['info_value']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Email Information -->
                        <?php if(count($email_info) > 0): ?>
                            <?php foreach($email_info as $email): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="<?php echo $email['icon_class']; ?>"></i>
                                    </div>
                                    <div>
                                        <h5><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $email['info_key']))); ?></h5>
                                        <p class="text-gray"><?php echo htmlspecialchars($email['info_value']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Hours Information -->
                        <?php if(count($hours_info) > 0): ?>
                            <?php foreach($hours_info as $hour): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="<?php echo $hour['icon_class']; ?>"></i>
                                    </div>
                                    <div>
                                        <h5><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $hour['info_key']))); ?></h5>
                                        <p class="text-gray"><?php echo htmlspecialchars($hour['info_value']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>