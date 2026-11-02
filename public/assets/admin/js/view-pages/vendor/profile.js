"use strict";


$("#customFileEg1").change(function () {
    readURL(this);
});

$("#generalSection").click(function() {
    $("#passwordSection").removeClass("active");
    $("#generalSection").addClass("active");
    $('html, body').animate({
        scrollTop: $("#generalDiv").offset().top
    }, 2000);
});

$("#passwordSection").click(function() {
    $("#generalSection").removeClass("active");
    $("#passwordSection").addClass("active");
    $('html, body').animate({
        scrollTop: $("#passwordDiv").offset().top
    }, 2000);
});

// Show Password
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".toggle-password").forEach(function(el){
        el.addEventListener("click", function () {
            let input = document.querySelector(this.getAttribute("toggle"));
            let icon = this.querySelector("i");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("tio-hidden-outlined");
                icon.classList.add("tio-visible-outlined");
            } else {
                input.type = "password";
                icon.classList.remove("tio-visible-outlined");
                icon.classList.add("tio-hidden-outlined");
            }
        });
    });
});