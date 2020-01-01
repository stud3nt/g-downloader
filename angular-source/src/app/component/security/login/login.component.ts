import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { AuthService } from "../../../service/auth.service";
import { LoginForm } from "../../../model/form/login-form";
import { JsonResponse } from "../../../model/json-response";
import {ToastrDataService} from "../../../service/data/toastr-data.service";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

	@Output() onLogin = new EventEmitter<boolean>();

	constructor(
		protected authService: AuthService,
		protected toastrDataService: ToastrDataService
	) { }

	public loginForm = new LoginForm();

	ngOnInit() {}

	public login(): void {
		this.authService.login(this.loginForm).subscribe((response: JsonResponse) => {
			this.toastrDataService.clear();

			if (response.success()) {
				this.onLogin.emit(true);
			} else {
				this.toastrDataService.addError('Login error', response.data);
			}
		}, (error) => {
			this.toastrDataService.addError('Login error', error);
		});
	}

}
