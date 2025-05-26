function toggleCommentForm(idPost) {
    const form = document.getElementById(`comment-form-${idPost}`);
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}

function toggleComments(postId) {
    const wrapper = document.getElementById(`comments-wrapper-${postId}`);
    if (wrapper) {
        wrapper.style.display = (wrapper.style.display === 'none' || wrapper.style.display === '') ? 'block' : 'none';
    }
}