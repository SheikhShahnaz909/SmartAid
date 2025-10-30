// models/Reporter.js
const mongoose = require('mongoose');
const bcrypt = require('bcrypt'); // Import bcrypt for hashing

const ReporterSchema = new mongoose.Schema({
    name: { // Changed from VName
        type: String,
        required: [true, 'Name is required'],
        maxlength: 50,
        trim: true
    },
    email: { // Changed from EmailID
        type: String, 
        required: [true, 'Email is required'],
        unique: true,
        lowercase: true,
        maxlength: 50
    },
    password: { // Changed from Password
        type: String,
        required: [true, 'Password is required']
    },
    phone: { // Changed from PhoneNo
        type: String,
        required: true
    },
    currentLocation: { // Changed from CurrentLocation
        type: String,
        required: true,
        maxlength: 25 
    },
    drivesNo: { // Changed from DrivesNo
        type: String, 
        required: true
    },
    role: { // Explicitly define the role
        type: String,
        default: 'reporter',
        enum: ['reporter']
    }
}, { timestamps: true }); 

// --- Mongoose Middleware (Hook) for Hashing Password ---
ReporterSchema.pre('save', async function (next) {
    if (!this.isModified('password')) {
        return next();
    }
    const salt = await bcrypt.genSalt(10);
    this.password = await bcrypt.hash(this.password, salt);
    next();
});

// --- Schema Method for Login Validation ---
ReporterSchema.methods.comparePassword = async function (candidatePassword) {
    return await bcrypt.compare(candidatePassword, this.password);
};

module.exports = mongoose.model('Reporter', ReporterSchema);