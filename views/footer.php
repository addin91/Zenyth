</main>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">
        Zenyth &copy; 2026 - Complexe Sportif de Rêve
        <?php if (!isset($IS_ADMIN_PAGE) || $IS_ADMIN_PAGE !== true): ?>
            &middot; <a href="index.php?action=admin" class="footer-admin-link">Espace administrateur</a>
        <?php endif; ?>
    </p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($IS_ADMIN_PAGE) && $IS_ADMIN_PAGE === true): ?>
    <script src="assets/js/admin.js"></script>
<?php else: ?>
    <script src="assets/js/app.js"></script>
<?php endif; ?>

</body>
</html>