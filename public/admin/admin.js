import React, {Component} from "react";
import "./admin.css";
import Dashboard from "./dashboard";
import SiteMetaData from "../site-metadata";

import axios from "axios";

class Admin extends Component {

	constructor(props) {

		super(props);
		this.state = {

			usernameError: false,
			passwordError: false,
			loggedIn: false

		}

		this.submitForm = this.submitForm.bind(this);
		this.validation = this.validation.bind(this);


	}

	
		validation(unItem, type) {

			let item = unItem.value.trim();


			if (item === "" || item.length > 20) {

				if (type === "username") {

					this.setState({ usernameError: true });
					return false;

				}

				if (type === "password") {

					this.setState({ passworError: true });
					return false;
					
				}

			}

			else {
	
				return true;

			}


		}

		submitForm(e) {

			let body = {};
			let username = e.target.username;
			let password = e.target.password;
			e.preventDefault();

			if (this.validation(username, "username") && this.validation(password, "password")) {

				body.username = username.value.trim();
				body.password = password.value.trim();
				username.disabled = true;
				password.disabled = true;
			
				axios.post("/login", body).then((res) => {

					if (res.data.usernameError) {

						this.setState({ usernameError: true });
						username.disabled = false;
						password.disabled = false;

					}

					if (res.data.passwordError) {

						this.setState({ passwordError: true });
						username.disabled = false;
						password.disabled = false;
						

					}

					if (res.data.passed) {

						username.value = "";
						password.value = "";
						username.disabled = false;
						password.disabled = false;
						this.setState({ passwordError: false, usernameError: false, loggedIn: true });

					}
	

				}
				).catch((e) => { console.log(e) });		

			}


		}



	componentDidMount() {

		document.title = "Krav Maga Tuzla | Admin";


	}

	render() {

		let content;

		if (this.state.loggedIn === true) {


			content = <Dashboard />

		}

		if (this.state.loggedIn === false) {

				let userError;
				let passError;

				if (this.state.usernameError) {

					userError = {color: "red"}

				}


				if (this.state.passwordError) {

					passError = {color: "red"};

				}

				content = <div className="login">
				
					<span>Krav Maga Tuzla - Admin</span>
					<form onSubmit={this.submitForm} >
				
						<label style={userError}>Username:</label>
						<input type="text" name="username" />
						<label style={passError}>Password:</label>
						<input type="password" name="password" />
						<input style={{ marginTop: "0.5rem", backgroundColor: "white", padding: "0.5rem", cursor:"pointer" }} type="submit" value="Login"/>


					</form>



				</div>;


		}
		
		


		return (<React.Fragment>
			<SiteMetaData />
			{content}

			</React.Fragment>
			);

	}


}

export default Admin;
