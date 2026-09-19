<?php $user = current_user(); ?>
<?php if ($user): ?>
    </main>
    <footer class="px-6 py-4 text-xs text-slate-400 border-t border-slate-200 bg-white">
      &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> — Prototype fonctionnel, données non persistantes.
    </footer>
  </div>
</div>
<?php endif; ?>
</body>
</html>
