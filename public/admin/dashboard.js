import React, {Component} from "react";
import "../components.css";
import { connect } from "react-redux";
import Rosinante from "../Rosinante";

import EditGallery from "./editor";

import axios from "axios";

const mapStateToProps = state => {

    return { editorPosts: state.editorPosts };

};

class Posts2 extends Component {

	constructor(props) {

		super(props);
		this.state = {

			waiting: false,
			rosinante: false,
			called: false,
			titleError: false

		}

		this.getData = this.getData.bind(this);
		this.ajaxCall = this.ajaxCall.bind(this);

		this.notVisible = this.notVisible.bind(this);
		this.scrollItem = React.createRef();

	}

	componentWillUnmount() {

			this.props.dispatch({ type: "GET_POSTS", posts: [], per_page: 5, current_page: 1, ran: false, last_page: Number.POSITIVE_INFINITY, total: 0, search: false });
			this.state.rosinante.removeRosinante();

	}

	notVisible() {

		

	}

	componentDidUpdate(prevProps, prevState) {

		
		if (this.props.editorPosts.ran === true && this.props.editorPosts.current_page < this.props.editorPosts.last_page && this.state.rosinante !== false && this.state.called === false) {

			
				this.state.rosinante.callRosinante();
				this.setState({ called: true });


		}

		if (prevProps.editorPosts.ran !== this.props.editorPosts.ran && prevProps.editorPosts.current_page <= this.props.editorPosts.current_page && this.props.editorPosts.posts.length > 0) {

			this.setState({ rosinante: new Rosinante(

				window,
				this.scrollItem.current,
				this.scrollItem.current,
				true,
				this.getData,
				this.notVisible,
				400,
				800,
				50,
				50
		
			)});

		}

	}

	getData(e) {

		let body = {};
		let title = this.props.editorPosts.search;
		body.per_page = this.props.editorPosts.per_page;
		body.current_page = this.props.editorPosts.current_page + 1;

		if (e) {

			e.preventDefault();

			if (e.target.title) {

				title = e.target.title.value.trim();

				if (title === "" || title.length > 100 || title === this.props.editorPosts.search) {

					this.setState({ titleError: true });

				}

				else {

					body.title = title;
					body.current_page = 1;
					this.setState({ titleError: false });
					
					this.ajaxCall(body);

				}

			}

			else {
	
				body.current_page = 1;
			
				this.ajaxCall(body);

			}

		}

		else {

			if (title === false) {
		
				if (this.props.editorPosts.current_page === this.props.editorPosts.last_page) {



				} else {
					
					this.ajaxCall(body);

				}

			}

			else {

				if (this.props.editorPosts.posts.length === this.props.editorPosts.total) {



				} else {

					body.title = title;
					
					this.ajaxCall(body);

				}


			}

		}

	}

	ajaxCall(body) {

		let title = body.title;
			
		if (!body.title) {

			title = false;

		}

		this.setState({ waiting: true });
		
		axios.post("/gallery/get", body)
			.then((res) => {

				if (res.data.titleError) {

					this.setState({ titleError: true });

				}

				else {

				this.setState({ waiting: false, titleError: false, called: false});
				let currentPage = res.data.pagination.current_page;
				let lastPage = res.data.pagination.last_page;

				if (currentPage === lastPage) {

					let newState = this.props.editorPosts.posts.slice();
					    res.data.data.map((post) => {

							newState.push(post);
							return post;

					    });
					
					this.props.dispatch({ type: "GET_POSTS", posts: newState, current_page: lastPage, ran: true, total: res.data.pagination.total, search: title, last_page: lastPage });

						let newState2;

						if (title !== this.props.editorPosts.search || body.current_page === 1) {

							newState2 = [];
					    		res.data.data.map((post) => {

								newState2.push(post);
								return post;

					    		});

							this.props.dispatch({ type: "GET_POSTS", posts: newState2, current_page: currentPage, ran: true, total: res.data.pagination.total, search: title, last_page: lastPage });


						}

						else {

							this.props.dispatch({ type: "GET_POSTS", posts: newState, current_page: currentPage, ran: true, total: res.data.pagination.total, search: title, last_page: lastPage });


						}


				}

				else {

					let newState;

					if (res.data.pagination.current_page === 1) {

					    newState = [];
					    res.data.data.map((post) => {

							newState.push(post);
							return post;

					    });

					}

					if (res.data.pagination.current_page > 1) {

					    newState = this.props.editorPosts.posts.slice();
					    res.data.data.map((post) => {

							newState.push(post);
							return post;

					    });


					}

					this.props.dispatch({ type: "GET_POSTS", posts: newState, current_page: currentPage, ran: true, total: res.data.pagination.total, search: title, last_page: lastPage});
				

				}

			}


			}).catch((e) => {console.log(e)});

	}

	render() {

		let waiting = <div className="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>;
		let titleError;

		if (this.state.waiting === false) {

			waiting = "";

		}

		if (this.state.titleError) {

			titleError = {color: "red"};

		}

		return (<div className="editor">
			<div className="editor-caller-button" onClick={this.getData}>Get Galleries</div>
			<form className="editor-caller-form" onSubmit={this.getData}>

			<input style={titleError} placeholder="Title" type="text" name="title" />
			<input type="submit" value="Search" />
			<div className="total-editor">{this.props.editorPosts.total}</div>
			</form>
			
			<div className="editor-items">
			{this.props.editorPosts.posts.map((post, index) => {

				return <EditGallery gallery={post} key={index}/>;
				

			})}
			<div className="waiter" ref={this.scrollItem} >{waiting}</div>
			</div>
			</div>
			);

	}

}

const Posts = connect(mapStateToProps)(Posts2);

class Dashboard extends Component {

	constructor(props) {

		super(props);
		this.state = {
	
			characters: 0,
			titleError: false,
			descriptionError: false,
			state: "images",
			posts: []

		}

		this.countChars = this.countChars.bind(this);
		this.submit = this.submit.bind(this);

		this.fileInput = React.createRef();

		this.changeFormStyle = this.changeFormStyle.bind(this);

		
	}

	changeFormStyle(e) {

		let name;

		if (e.target.className === "form-button images") {

			name = "images";

		}

		if (e.target.className === "form-button video") {

			name = "images";

		}

		this.setState({state: name});

	}

	countChars(e) {

		let text = e.target.value;

		this.setState({ characters: text.length });
		
	}

	validation(item, type) {

		let value = item.value.trim();

		if (type === "title") {

			if (value === "" || value.length > 100) {

				this.setState({titleError: true});
				return false;

			}

			else {

				this.setState({titleError: false});
				return true;

			}

		}


		else if (type === "description") {


			if (value === "" || this.state.characters > 500) {

				this.setState({descriptionError: true});
				return false;

			}

			else {

				this.setState({descriptionError: false});
				return true;

			}

		}
	

	}

	submit(e) {

		e.preventDefault();
		let formData = new FormData();
		let title = e.target.title;
		let description = e.target.description;
		let type = this.state.state;

		if (this.validation(title, "title") && this.validation(description, "description") && (this.fileInput.current.files.length !== 0 || this.fileInput.current.files.length > 30) ) {

			for (let i = 0; i < this.fileInput.current.files.length; i++) {

				formData.append('files[]', this.fileInput.current.files[i], this.fileInput.current.files[i].name);

			}

			formData.append('title', title.value.trim());
			formData.append('description', description.value.trim());
			formData.append('date', new Date());
			formData.append('type', type);

			title.disabled = true;
			description.disabled = true;

			axios({method: "post", url:"/gallery/post", data: formData, headers: {
    contentType: false,
	processData: false
  }})
			.then((res) => {

				if (res.data.titleError) {

					this.setState({ titleError: true });

				}

				else if (res.data.descriptionError) {

					this.setState({ descriptionError: true });

				}


				else {
					this.setState({ descriptionError: false, titleError: false });
					title.value = "";
					description.value = "";
					this.fileInput.current.value = "";
					title.disabled = false;
					description.disabled = false;
						
				}

			}).catch((e) => {console.log(e)});
			
			


		}
		


	}

	componentDidMount() {

		


	}

	render() {

		let textAreaError;
		let titleError;
		let descriptionError;
		let imageStyle;
		let videoStyle;
		let label;
		let multiple;

		if (this.state.characters > 500) {

			textAreaError = {color: "red"};

		}

		if (this.state.titleError) {

			titleError = {color: "red"};

		}


		if (this.state.descriptionError) {

			descriptionError = {color: "red"}

		}

		if (this.state.state === "images") {


			imageStyle = { opacity: 0.8 };
			videoStyle = {};
			label = "Images:";
			multiple = true;

		}

		if (this.state.state === "video") {


			videoStyle = { opacity: 0.8 };
			imageStyle = {};
			label = "Video:";
			multiple = false;
			

		}


		return (<div className="dashboard">
			<div className="add-gallery">
				<form onSubmit={this.submit} >
				<div className="form-button-container">
				<div onClick={this.changeFormStyle} style={imageStyle} className="form-button images">Images</div>
				<div onClick={this.changeFormStyle} style={videoStyle} className="form-button video">Video</div>
				</div>
				<label style={titleError}>Title</label>
				<input type="text" name="title" />
				<label style={descriptionError}>Description</label>
				<textarea name="description" onKeyUp={this.countChars} />
				<label style={textAreaError}>{this.state.characters}/{500}</label>
				<label>{label}</label>
				<input accept="image/x-png, image/gif, image/jpeg" multiple={multiple} style={{ alignSelf: "center", width: "100%"}} type="file" name="files" ref={this.fileInput}  />
				<input style={{ marginTop: "0.5rem", backgroundColor: "white", padding: "0.5rem", cursor:"pointer" }} type="submit" value="Submit" />
				
				</form>
			</div>

			<Posts />
			</div>
			);

	}


}

export default Dashboard;
