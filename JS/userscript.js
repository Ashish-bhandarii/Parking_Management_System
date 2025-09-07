   // Tab switching functionality
   document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      document.querySelector(`#${tab.id}-content`).classList.add('active');
    });
  });
  
  // JavaScript function to toggle the profile popup visibility
// Toggle Profile Popup
function toggleProfilePopup() {
  const popup = document.getElementById('profile-popup');
  popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
};
document.addEventListener("DOMContentLoaded", () => {
  const tabContents = document.querySelectorAll(".tab-content");

  let currentIndex = 0;

  function slideContent() {
    // Hide all content sections
    tabContents.forEach(content => content.classList.remove("active"));

    // Show the current content section
    tabContents[currentIndex].classList.add("active");

    // Update the index for the next slide
    currentIndex = (currentIndex + 1) % tabContents.length; // Loops back to the first section
  }

  // Initial call to set the first content section as active
  slideContent();

  // Set the content to slide every 5 seconds (5000 milliseconds)
  setInterval(slideContent, 5000);
});
