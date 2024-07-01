<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enquiry Form</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<form id="enquiry_form">
    <label>Name</label><br>
    <input type="text" name="name"><br><br>
    <label>Email</label><br>
    <input type="email" name="email"><br><br>
    <label>Phone Number</label><br>
    <input type="number" name="phone"><br><br>
    <label>Message</label><br>
    <textarea name="message"></textarea><br />
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wp_rest'); ?>"><!-- Add nonce field here -->
    <button type="submit">Submit form</button>
</form>


<script>
    jQuery(document).ready(function($){
        $("#enquiry_form").submit(function(event) {
            event.preventDefault();
            var form = $(this);
            alert(form.serialize()); // Ensure this alert works
            console.log(form.serialize()); // Add a console log for debugging

            $.ajax({
                type: "POST",
                url: "<?php echo get_rest_url(null, 'v1/contact-form/submit'); ?>",
                data: form.serialize(),
                success: function(response) {
                    console.log("Success:", response);
                    alert("Form submitted successfully!");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error:", textStatus, errorThrown);
                    alert("An error occurred. Please try again.");
                }
            });
        });
    });
</script>

</body>
</html>
