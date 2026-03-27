
<?php
   require 'includes.php';
?>

<div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-100 mx-4">
    <p class="text-center mb-4">
        <?php include 'includes/components/logo.php'?>
    </p>
    

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold">Welcome Back</h2>
        <?php  if($basename !== "login.php"):?>
            <button onclick="closeModal('loginModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        <?php endif;?>
    </div>
    <?php if (isset($_GET['success']) || isset($_GET['msg'])): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 font-medium">
            <?php echo htmlspecialchars($_GET['success'] ?? $_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 border border-red-100">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" class="space-y-5" action="./actions/login_process.php">
        <div>
            <label class="block text-sm font-semibold mb-2">Email</label>
            <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Enter email" required>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="••••••••" required>
        </div>
  
            <small class="float-right -mt-10"><a href="forgot-password.php" class="text-xs hover:underline m-0 p-0">Forgot Password?</a></small>

        <div class="flex justify-start gap-1 items-center">
            <input type="checkbox" name="remember_me" id="remember_me" value="checked" <?php $_POST['remember_me'] ?? ''?>>
                <label class="text-xs" for="remember_me">Remeber Me</label>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">Sign In</button>
    </form>

    <p class="text-center mt-6 text-slate-500 text-sm">
        New here? <a href="signup.php" class="text-blue-600 font-semibold hover:underline">Create an account</a>
    </p>
</div>