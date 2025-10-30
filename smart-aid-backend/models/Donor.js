// models/Donor.js
const mongoose = require('mongoose');
const bcrypt = require('bcrypt'); // Import bcrypt for hashing

const DonorSchema = new mongoose.Schema({
    name: {
        type: String,
        required: [true, 'Name is required'],
        maxlength: 50,
        trim: true
    },
    email: {
        type: String,
        required: [true, 'Email is required'],
        unique: true,
        lowercase: true,
        trim: true,
        // Enforce the required email format from your frontend validation
        match: [/@(gmail|hotmail)\.com$/, 'Email must be a valid @gmail.com or @hotmail.com address']
    },
    password: { // Will store the secure BCrypt hash
        type: String,
        required: [true, 'Password is required']
    },
    phone: {
        type: String, // Store as String to preserve leading zeros and handle formatting
        required: [true, 'Phone number is required'],
        match: [/^\d{10}$/, 'Phone number must be exactly 10 digits'] 
    },
    location: { // Changed from Address
        type: String,
        required: [true, 'Location is required'],
        maxlength: 60, 
        trim: true
    },
    role: { // Explicitly define the role
        type: String,
        default: 'donor',
        enum: ['donor']
    }
}, { timestamps: true });

// --- Mongoose Middleware (Hook) for Hashing Password ---
// Note: We use a pre-save hook here for security.
DonorSchema.pre('save', async function (next) {
    if (!this.isModified('password')) {
        return next();
    }
    // Hash the password with a salt round of 10
    const salt = await bcrypt.genSalt(10);
    this.password = await bcrypt.hash(this.password, salt);
    next();
});

// --- Schema Method for Login Validation ---
// Compares the login password with the stored hash
DonorSchema.methods.comparePassword = async function (candidatePassword) {
    return await bcrypt.compare(candidatePassword, this.password);
};

module.exports = mongoose.model('Donor', DonorSchema);// models/Donor.js
const mongoose = require('mongoose');
const bcrypt = require('bcrypt'); // Import bcrypt for hashing

const DonorSchema = new mongoose.Schema({
    name: {
        type: String,
        required: [true, 'Name is required'],
        maxlength: 50,
        trim: true
    },
    email: {
        type: String,
        required: [true, 'Email is required'],
        unique: true,
        lowercase: true,
        trim: true,
        // Enforce the required email format from your frontend validation
        match: [/@(gmail|hotmail)\.com$/, 'Email must be a valid @gmail.com or @hotmail.com address']
    },
    password: { // Will store the secure BCrypt hash
        type: String,
        required: [true, 'Password is required']
    },
    phone: {
        type: String, // Store as String to preserve leading zeros and handle formatting
        required: [true, 'Phone number is required'],
        match: [/^\d{10}$/, 'Phone number must be exactly 10 digits'] 
    },
    location: { // Changed from Address
        type: String,
        required: [true, 'Location is required'],
        maxlength: 60, 
        trim: true
    },
    role: { // Explicitly define the role
        type: String,
        default: 'donor',
        enum: ['donor']
    }
}, { timestamps: true });

// --- Mongoose Middleware (Hook) for Hashing Password ---
// Note: We use a pre-save hook here for security.
DonorSchema.pre('save', async function (next) {
    if (!this.isModified('password')) {
        return next();
    }
    // Hash the password with a salt round of 10
    const salt = await bcrypt.genSalt(10);
    this.password = await bcrypt.hash(this.password, salt);
    next();
});

// --- Schema Method for Login Validation ---
// Compares the login password with the stored hash
DonorSchema.methods.comparePassword = async function (candidatePassword) {
    return await bcrypt.compare(candidatePassword, this.password);
};

module.exports = mongoose.model('Donor', DonorSchema);