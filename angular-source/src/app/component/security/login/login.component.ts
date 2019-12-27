import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { AuthService } from "../../../service/auth.service";
import { LoginForm } from "../../../model/form/login-form";
import { JsonResponse } from "../../../model/json-response";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

	@Output() onLogin = new EventEmitter<boolean>();

	public loginError: string = '';

	constructor(
		protected authService: AuthService
	) { }

	public loginForm = new LoginForm();

	ngOnInit() {}

	public login(): void {
		this.authService.login(this.loginForm).subscribe((response: JsonResponse) => {
			this.loginError = '';

			if (response.success()) {
				this.onLogin.emit(true);
			} else {
				this.loginError = response.data;
			}
		}, (error) => {

		});
	}

}
