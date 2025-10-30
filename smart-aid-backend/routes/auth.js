const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt'); // Hashing
const crypto = require('crypto'); // For generating the unique ID (DID/VID)

// Import the Mongoose Models
const Donor = require('../models/Donor');
const Reporter = require('../models/Reporter');

// =========================================================
// POST /api/donor/signup - Handles Donor Registration
// Corresponds to the action in donor_signup.html
// =========================================================
router.post('/donor/signup', async (req, res) => {
    try {
        // 1. Get Data from Frontend Form Submission
        const { name, email, password, phone, location } = req.body;

        // Note: Frontend forms use lowercase names (name, email), 
        // so we map them to the PascalCase model fields (Name, EmailID).

        // 2. Check if user already exists
        const existingDonor = await Donor.findOne({ EmailID: email });
        if (existingDonor) {
            // Error response to frontend (matching error logic in donor_signup.html)
            return res.status(409).json({ message: "An account with this email already exists." });
        }

        // 3. SECURELY HASH THE PASSWORD
        const salt = await bcrypt.genSalt(10); // Generate salt
        const hashedPassword = await bcrypt.hash(password, salt); // Hash password

        // 4. Generate Unique ID (DID)
        // Using a short, random hex string for the Varchar ID (DID)
        const newDID = 'D' + crypto.randomBytes(4).toString('hex').toUpperCase(); 

        // 5. Create the new Donor object
        const newDonor = new Donor({
            DID: newDID,
            Name: name,
            Address: location, // Map 'location' from form to 'Address'
            PhoneNo: phone, 
            EmailID: email,
            Password: hashedPassword // Store the HASH
        });

        // 6. Save to Database
        const savedDonor = await newDonor.save();

        // Success response
        return res.status(201).json({ 
            message: "Donor account created successfully.", 
            user: { id: savedDonor.DID, email: savedDonor.EmailID } 
        });

    } catch (error) {
        console.error("Donor Sign-up Error:", error);
        // Generic server error response
        return res.status(500).json({ message: "Server error during registration." });
    }
});

// TODO: You will add the POST /api/reporter/signup route here later.

module.exports = router;